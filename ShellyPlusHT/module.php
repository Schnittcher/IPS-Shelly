<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusHT extends ShellyModule
{
    public static $Variables = [
        ['Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true],
        ['Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['BatteryVolt', 'Battery Volt', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
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
                    $this->SetValue('Reachable', $Payload);
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('temperature:0', $Payload['params'])) {
                            $this->SetValue('Temperature', $Payload['params']['temperature:0']['tC']);
                        }
                        if (array_key_exists('humidity:0', $Payload['params'])) {
                            $this->SetValue('Humidity', $Payload['params']['humidity:0']['rh']);
                        }
                        if (array_key_exists('devicepower:0', $Payload['params'])) {
                            $this->SetValue('Battery', $Payload['params']['devicepower:0']['battery']['percent']);
                            $this->SetValue('BatteryVolt', $Payload['params']['devicepower:0']['battery']['V']);
                        }
                    }
                }
            }
        }
    }
}
