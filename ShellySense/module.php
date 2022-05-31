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
            $data = json_decode($JSONString);

            switch ($data->DataID) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data->Buffer);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
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
