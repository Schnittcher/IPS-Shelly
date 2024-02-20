<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusUni extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input2Count', 'Input 3 Count Total', VARIABLETYPE_INTEGER, '', [], '', false, true],
        ['Input2Frequency', 'Input 3 Frequency', VARIABLETYPE_FLOAT, '~Hertz', [], '', false, true],
        ['Temperature100', 'External Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature101', 'External Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature102', 'External Temperature 3', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature103', 'External Temperature 4', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Temperature104', 'External Temperature 5', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Humidity100', 'External Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true],
        ['Voltmeter100', 'External Voltmeter', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State0':
                $this->SwitchMode(0, $Value);
                break;
            case 'State1':
                $this->SwitchMode(1, $Value);
                break;
            }
    }

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
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('switch:0', $Payload['params'])) {
                            $service = $Payload['params']['switch:0'];
                            if (array_key_exists('output', $service)) {
                                $this->SetValue('State0', $service['output']);
                            }
                        }
                        if (array_key_exists('switch:1', $Payload['params'])) {
                            $service = $Payload['params']['switch:1'];
                            if (array_key_exists('output', $service)) {
                                $this->SetValue('State1', $service['output']);
                            }
                        }

                        if (array_key_exists('input:0', $Payload['params'])) {
                            $service = $Payload['params']['input:0'];
                            if (array_key_exists('state', $service)) {
                                $this->SetValue('Input0', $service['state']);
                            }
                        }

                        if (array_key_exists('input:1', $Payload['params'])) {
                            $service = $Payload['params']['input:1'];
                            if (array_key_exists('state', $service)) {
                                $this->SetValue('Input1', $service['state']);
                            }
                        }

                        if (array_key_exists('input:2', $Payload['params'])) {
                            $service = $Payload['params']['input:2'];
                            if (array_key_exists('counts', $service)) {
                                $this->SetValue('Input2Count', $service['counts']['total']);
                            }
                            if (array_key_exists('freq', $service)) {
                                $this->SetValue('Input2Frequency', $service['freq']);
                            }
                        }
                    }
                    for ($i = 100; $i <= 104; $i++) {
                        $temperatureIndex = 'temperature:' . $i;
                        if (array_key_exists($temperatureIndex, $Payload['params'])) {
                            $temperature = $Payload['params'][$temperatureIndex];
                            if (array_key_exists('tC', $temperature)) {
                                $this->SetValue('Temperature' . $i, $temperature['tC']);
                            }
                        }
                    }
                    if (array_key_exists('voltmeter:100', $Payload['params'])) {
                        $voltmeter = $Payload['params']['voltmeter:100'];
                        if (array_key_exists('voltage', $voltmeter)) {
                            $this->SetValue('Voltmeter100', $voltmeter['voltage']);
                        }
                    }
                    if (array_key_exists('humidity:100', $Payload['params'])) {
                        $humidity = $Payload['params']['humidity:100'];
                        if (array_key_exists('rh', $humidity)) {
                            $this->SetValue('Humidity100', $humidity['rh']);
                        }
                    }
                }
            }
        }
    }

    private function SwitchMode(int $switch, bool $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $switch, 'on' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}