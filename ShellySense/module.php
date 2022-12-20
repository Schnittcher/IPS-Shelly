<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellySense extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', [], '', false, true],
        ['Shelly_Charger', 'External Charger', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true],
        ['Shelly_Lux', 'Lux', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/sensor/motion*', $Buffer->Topic)) {
                    $this->SendDebug('Motion Payload', $Buffer->Payload, 0);
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
                    $this->SendDebug('Charger Payload', $Buffer->Payload, 0);
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
                    $this->SendDebug('Temperature Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/humidity*', $Buffer->Topic)) {
                    $this->SendDebug('Humidity Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Humidity', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/lux*', $Buffer->Topic)) {
                    $this->SendDebug('Lux Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Lux', $Buffer->Payload);
                }
                if (fnmatch('*/sensor/battery*', $Buffer->Topic)) {
                    $this->SendDebug('Battery Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Battery', $Buffer->Payload);
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
                    $this->SendDebug('Online Payload', $Buffer->Payload, 0);
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
