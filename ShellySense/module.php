<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellySense extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', [], '', false, true, false],
        ['Shelly_Charger', 'External Charger', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Shelly_Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true, false],
        ['Shelly_Lux', 'Lux', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true, false],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true, false],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
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
                if (fnmatch('*/sensor/motion*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Motion', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Motion', false);
                            break;
                        default:
                            $this->SendDebug('Motion Sensor', 'Undefined Payload: ' . $Buffer->Payload, 0);
                            break;
                    }
                }
                if (fnmatch('*/sensor/charger*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Charger', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Charger', false);
                            break;
                        default:
                            $this->SendDebug('External Charger', 'Undefined Payload: ' . $Buffer->Payload, 0);
                            break;
                    }
                }
                if (fnmatch('*/sensor/temperature*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/humidity*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Humidity', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/lux*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Lux', $Buffer->Payload);
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
                            $this->zeroingValues();
                            break;
                    }
                }
            }
        }
    }
}
