<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Gen3ShellyI4 extends ShellyModule
{
    public static $Variables = [
        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input2', 'Input 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input3', 'Input 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false],
        ['Temperature100', 'External Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature101', 'External Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature102', 'External Temperature 3', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature103', 'External Temperature 4', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature104', 'External Temperature 5', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Input100State', 'External Input State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input100Percent', 'External Input Percent', VARIABLETYPE_FLOAT, 'Shelly.Input.Percent', [], '', false, true, false],
        ['Voltmeter100', 'External Voltmeter', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
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
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'][0];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
                        }
                        for ($i = 0; $i <= 3; $i++) {
                            $inputIndex = 'input:' . $i;
                            if (array_key_exists($inputIndex, $Payload['params'])) {
                                $input = $Payload['params'][$inputIndex];
                                if (array_key_exists('state', $input)) {
                                    $this->SetValue('Input' . $i, $input['state']);
                                }
                            }
                        }
                        //External Sensor Addon
                        for ($i = 100; $i <= 104; $i++) {
                            $temperatureIndex = 'temperature:' . $i;
                            if (array_key_exists($temperatureIndex, $Payload['params'])) {
                                $temperature = $Payload['params'][$temperatureIndex];
                                if (array_key_exists('tC', $temperature)) {
                                    $this->SetValue('Temperature' . $i, $temperature['tC']);
                                }
                            }
                        }
                        //External Sensor Addon
                        if (array_key_exists('humidity:100', $Payload['params'])) {
                            $humidity = $Payload['params']['humidity:100'];
                            if (array_key_exists('rH', $humidity)) {
                                $this->SetValue('Humidity100', $humidity['rH']);
                            }
                        }
                        //External Sensor Addon
                        if (array_key_exists('input:100', $Payload['params'])) {
                            $input = $Payload['params']['input:100'];
                            if (array_key_exists('state', $input)) {
                                $this->SetValue('Input100State', $input['state']);
                            }
                            if (array_key_exists('percent', $input)) {
                                $this->SetValue('Input100Percent', $input['percent']);
                            }
                        }
                        //External Sensor Addon
                        if (array_key_exists('voltmeter:100', $Payload['params'])) {
                            $voltmeter = $Payload['params']['voltmeter:100'];
                            if (array_key_exists('voltage', $voltmeter)) {
                                $this->SetValue('Voltmeter100', $voltmeter['voltage']);
                            }
                        }
                    }
                }
            }
        }
    }
}
