<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellySmoke extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Smoke', 'Smoke', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

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
                if (fnmatch('*/sensor/temperature*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/smoke*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Smoke', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/battery*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Battery', $Buffer->Payload);
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Reachable', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Reachable', false);
                            break;
                    }
                }
            }
        }
    }
}
