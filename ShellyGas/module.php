<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyGas extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Operation', 'Operation', VARIABLETYPE_INTEGER, 'Shelly.Gas.Operation', [], '', false, true,false],
        ['Shelly_Gas', 'Gas', VARIABLETYPE_INTEGER, 'Shelly.Gas', [], '', false, true,false],
        ['Shelly_SelfTest', 'Self Test', VARIABLETYPE_INTEGER, 'Shelly.Gas.SelfTest', [], '', false, true,false],
        ['Shelly_Concentration', 'Concentration', VARIABLETYPE_INTEGER, '', [], '', false, true,false],
        ['Shelly_Control', 'Control', VARIABLETYPE_INTEGER, 'Shelly.Gas.Control', [], '', true, true,false],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true,false]
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProfileIntegerEx('Shelly.Gas.Operation', 'TurnLeft', '', '', [
            [0, $this->Translate('Unknown'),  '', 0xFFA500],
            [1, $this->Translate('Warmup'),  '', 0xDFDF1c],
            [2, $this->Translate('Normal'),  '', 0x00FF00],
            [3, $this->Translate('Fault'),  '', 0xFF0000],
        ]);

        $this->RegisterProfileIntegerEx('Shelly.Gas', 'Gas', '', '', [
            [0, $this->Translate('Unknown'),  '', 0xFFA500],
            [1, $this->Translate('No Alarm'),  '', 0x00FF00],
            [2, $this->Translate('Mild'),  '', 0xFFA500],
            [3, $this->Translate('Heavy'),  '', 0xFF0000],
            [4, $this->Translate('Test'),  '', 0xDFDF1c],
        ]);

        $this->RegisterProfileIntegerEx('Shelly.Gas.SelfTest', 'Shuffle', '', '', [
            [0, $this->Translate('Not Completed'),  '', 0xFF0000],
            [1, $this->Translate('Completed'),  '', 0x00FF00],
            [2, $this->Translate('Running'),  '', 0x00FF00],
            [3, $this->Translate('Pending'),  '', 0x00FF00],
        ]);

        $this->RegisterProfileIntegerEx('Shelly.Gas.Control', 'Remote', '', '', [
            [0, $this->Translate('Start Self Test'),  '', -1],
            [1, $this->Translate('Mute'),  '', -1],
            [2, $this->Translate('Unmute'),  '', -1],
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

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_Control':
                switch ($Value) {
                    case 0:
                        $this->sendMQTT('shellies/shellygas-<deviceid>/sensor/start_self_test', '');
                        break;
                    case 1:
                        $this->sendMQTT('shellies/shellygas-<deviceid>/sensor/mute', '');
                        break;
                    case 2:
                        $this->sendMQTT('shellies/shellygas-<deviceid>/sensor/unmute', '');
                        break;
                    default:
                        $this->LogMessage($this->Translate('Invalid Value for Control: ') . $Value, KL_ERROR);
                        break;
                }
                break;
            default:
                $this->LogMessage($this->Translate('Invalid Ident for RequestAction: ') . $Ident, KL_ERROR);
                break;
            }
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
                if (fnmatch('*/sensor/operation*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'unknown':
                            $this->SetValue('Shelly_Operation', 0);
                            break;
                        case 'warmup':
                            $this->SetValue('Shelly_Operation', 1);
                            break;
                        case 'normal':
                            $this->SetValue('Shelly_Operation', 2);
                            break;
                        case 'fault':
                            $this->SetValue('Shelly_Operation', 3);
                            break;
                        default:
                        $this->LogMessage($this->Translate('Invalid Value for Operation: ') . $Buffer->Payload, KL_ERROR);
                            break;
                    }
                }
                if (fnmatch('*/sensor/gas*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'unknown':
                            $this->SetValue('Shelly_Gas', 0);
                            break;
                        case 'none':
                            $this->SetValue('Shelly_Gas', 1);
                            break;
                        case 'mild':
                            $this->SetValue('Shelly_Gas', 2);
                            break;
                        case 'heavy':
                            $this->SetValue('Shelly_Gas', 3);
                            break;
                        case 'test':
                            $this->SetValue('Shelly_Gas', 4);
                            break;
                        default:
                            $this->LogMessage($this->Translate('Invalid Value for Gas: ') . $Buffer->Payload, KL_ERROR);
                            break;
                    }
                }
                if (fnmatch('*/sensor/self_test*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'not_completed':
                            $this->SetValue('Shelly_SelfTest', 0);
                            break;
                        case 'completed':
                            $this->SetValue('Shelly_SelfTest', 1);
                            break;
                        case 'running':
                            $this->SetValue('Shelly_SelfTest', 2);
                            break;
                        case 'pending':
                            $this->SetValue('Shelly_SelfTest', 3);
                            break;
                        case 'test':
                            $this->SetValue('Shelly_SelfTest', 4);
                            break;
                        default:
                            $this->LogMessage($this->Translate('Invalid Value for Self Test: ') . $Buffer->Payload, KL_ERROR);
                            break;
                    }
                }

                if (fnmatch('*/sensor/concentration*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Concentration', $Buffer->Payload);
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
            }
        }
    }

    private function SwitchMode(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}
