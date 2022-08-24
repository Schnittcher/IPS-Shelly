<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusHT extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['Shelly_BatteryVolt', 'Battery Volt', VARIABLETYPE_INTEGER, '~Volt', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString, true);
            switch ($data['DataID']) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data['Buffer']);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
            }

            $this->SendDebug('MQTT Topic', $Buffer['Topic'], 0);

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Shelly_Reachable', $Payload);
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('temperature:0"', $Payload['params'])) {
                            $switch = $Payload['params']['temperature:0"'];
                            if (array_key_exists('tC', $switch)) {
                                $this->SetValue('Shelly_Temperature', $switch['tC']);
                            }
                            if (array_key_exists('humidity:0', $switch)) {
                                $this->SetValue('Shelly_Humidity', $switch['rh']);
                            }
                            if (array_key_exists('devicepower:0', $switch)) {
                                if (array_key_exists('devicepower:0', $switch['devicepower:0'])) {
                                    $this->SetValue('Shelly_Battery', $switch['devicepower:0']['percent']);
                                    $this->SetValue('Shelly_BatteryVolt', $switch['devicepower:0']['V']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
