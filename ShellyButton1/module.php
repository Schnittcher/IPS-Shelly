<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyButton1 extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Input', 'Input', VARIABLETYPE_INTEGER, 'Shelly.Button1Input', [], '', false, true, false],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true, false],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProfileIntegerEx('Shelly.Button1Input', 'ArrowRight', '', '', [
            [0, $this->Translate('shortpush'),  '', 0x08f26e],
            [1, $this->Translate('double shortpush'),  '', 0x07da63],
            [2, $this->Translate('triple shortpush'),  '', 0x06c258],
            [3, $this->Translate('longpush'),  '', 0x06a94d],
        ]);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');
    }

    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/input_event/0', $Buffer->Topic)) {
                    $Payload = json_decode($Buffer->Payload);
                    switch ($Payload->event) {
                        case 'S':
                            $this->SetValue('Shelly_Input', 0);
                            break;
                        case 'SS':
                            $this->SetValue('Shelly_Input', 1);
                            break;
                        case 'SSS':
                            $this->SetValue('Shelly_Input', 2);
                            break;
                        case 'L':
                            $this->SetValue('Shelly_Input', 3);
                            break;
                    }
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Reachable', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Reachable', false);
                            $this->zeroingValues();
                            break;
                    }
                }
                if (fnmatch('*/sensor/battery*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Battery', $Buffer->Payload);
                }
            }
        }
    }
}