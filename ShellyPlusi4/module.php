<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusi4 extends ShellyModule
{
    public static $Variables = [
        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input2', 'Input 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input3', 'Input 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true],
        ['Temperature100', 'External Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature101', 'External Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature102', 'External Temperature 3', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature103', 'External Temperature 4', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature104', 'External Temperature 5', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Input100State', 'External Input State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input100Percent', 'External Input Percent', VARIABLETYPE_BOOLEAN, 'Shelly.Input.Percent', [], '', false, true],
        ['Voltmeter100', 'External Voltmeter', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString, true);
            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
            }

            $this->SendDebug('MQTT Topic', $Buffer['Topic'], 0);

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'];
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
