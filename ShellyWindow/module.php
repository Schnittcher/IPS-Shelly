<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyWindow extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Window', [], '', false, true],
        ['Shelly_Lux', 'Lux', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', ['DW2'], '', false, true],
        ['Shelly_Vibration', 'Vibration', VARIABLETYPE_BOOLEAN, '~Alert', ['DW2'], '', false, true],
        ['Shelly_Tilt', 'Tilt', VARIABLETYPE_INTEGER, '', ['DW2'], '', false, true],
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
                if (fnmatch('*/state', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'close':
                            $this->SetValue('Shelly_State', false);
                            break;
                        case 'open':
                            $this->SetValue('Shelly_State', true);
                            break;
                        default:
                            $this->SendDebug('Invalid Payload for State', $Buffer->Payload, 0);
                            break;
                        }
                }
                if (fnmatch('*/lux', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Lux', $Buffer->Payload);
                }
                if (fnmatch('*/battery', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Battery', $Buffer->Payload);
                }
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/vibration', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 1:
                            $this->SetValue('Shelly_Vibration', true);
                            break;
                        case 0:
                            $this->SetValue('Shelly_Vibration', false);
                            break;
                        default:
                            $this->SendDebug('Invalid Payload for Vibration', $Buffer->Payload, 0);
                            break;
                        }
                }
                if (fnmatch('*/tilt', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Tilt', $Buffer->Payload);
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
