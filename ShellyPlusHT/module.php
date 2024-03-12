<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusHT extends ShellyModule
{
    public static $Variables = [
        ['Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true, false],
        ['Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true, false],
        ['BatteryVolt', 'Battery Volt', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString, true);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
            }

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                    if (!$Payload) {
                        $this->zeroingValues();
                    }
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
