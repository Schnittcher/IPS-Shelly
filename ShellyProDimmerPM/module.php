<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyProDimmerPM extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', true, true, false],
        ['Brightness0', 'Brightness 1', VARIABLETYPE_INTEGER, '~Intensity.100', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', true, true, false],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['TotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['Errors0', 'Errors 1', VARIABLETYPE_STRING, '', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['DeviceTemperature0', 'Device Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', true, true, false],
        ['Brightness1', 'Brightness 2', VARIABLETYPE_INTEGER, '~Intensity.100', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', true, true, false],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['TotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['Error10', 'Errors 2', VARIABLETYPE_STRING, '', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],
        ['DeviceTemperature1', 'Device Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', ['shellyprodimmer1pm', 'shellyprodimmer2pm'], '', false, true, false],

        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true,  false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true,  false],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
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
            case 'Brightness0':
                $this->SetBrightness(0, $Value);
                break;
            case 'Brightness1':
                $this->SetBrightness(1, $Value);
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
                        if (array_key_exists('input:0', $Payload['params'])) {
                            if (array_key_exists('state', $Payload['params']['input:0'])) {
                                $this->SetValue('Input0', $Payload['params']['input:0']['state']);
                            }
                        }
                        if (array_key_exists('input:1', $Payload['params'])) {
                            if (array_key_exists('state', $Payload['params']['input:1'])) {
                                $this->SetValue('Input1', $Payload['params']['input:1']['state']);
                            }
                        }

                        if (array_key_exists('light:0', $Payload['params'])) {
                            $service = $Payload['params']['light:0'];
                            if (array_key_exists('output', $service)) {
                                $this->SetValue('State0', $service['output']);
                            }
                            if (array_key_exists('brightness', $service)) {
                                $this->SetValue('Brightness0', $service['brightness']);
                            }
                            if (array_key_exists('apower', $service)) {
                                $this->SetValue('Power0', $service['apower']);
                            }
                            if (array_key_exists('voltage', $service)) {
                                $this->SetValue('Voltage0', $service['voltage']);
                            }
                            if (array_key_exists('current', $service)) {
                                $this->SetValue('Current0', $service['current']);
                            }
                            if (array_key_exists('aenergy', $service)) {
                                if (array_key_exists('total', $service['aenergy'])) {
                                    $this->SetValue('TotalEnergy0', $service['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('temperature', $Payload)) {
                                if (array_key_exists('tC', $Payload['temperature'])) {
                                    $this->SetValue('DeviceTemperature0', $Payload['temperature']['tC']);
                                }
                            }
                            if (array_key_exists('errors', $service)) {
                                $errors = implode(',', $service['errors']);
                                $this->SetValue('Errors0', $errors);
                            }
                        }
                        if (array_key_exists('light:1', $Payload['params'])) {
                            $service = $Payload['params']['light:1'];
                            if (array_key_exists('output', $service)) {
                                $this->SetValue('State1', $service['output']);
                            }
                            if (array_key_exists('brightness', $service)) {
                                $this->SetValue('Brightness1', $service['brightness']);
                            }
                            if (array_key_exists('apower', $service)) {
                                $this->SetValue('Power1', $service['apower']);
                            }
                            if (array_key_exists('voltage', $service)) {
                                $this->SetValue('Voltage1', $service['voltage']);
                            }
                            if (array_key_exists('current', $service)) {
                                $this->SetValue('Current1', $service['current']);
                            }
                            if (array_key_exists('aenergy', $service)) {
                                if (array_key_exists('total', $service['aenergy'])) {
                                    $this->SetValue('TotalEnergy1', $service['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('temperature', $Payload)) {
                                if (array_key_exists('tC', $Payload['temperature'])) {
                                    $this->SetValue('DeviceTemperature1', $Payload['temperature']['tC']);
                                }
                            }
                            if (array_key_exists('errors', $service)) {
                                $errors = implode(',', $service['errors']);
                                $this->SetValue('Errors1', $errors);
                            }
                        }
                    }
                }
                if (fnmatch('*/status/light:0', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State0', $Payload['output']);
                    }
                    if (array_key_exists('brightness', $Payload)) {
                        $this->SetValue('Brightness0', $Payload['brightness']);
                    }
                    if (array_key_exists('apower', $Payload)) {
                        $this->SetValue('Power0', $Payload['apower']);
                    }
                    if (array_key_exists('voltage', $Payload)) {
                        $this->SetValue('Voltage0', $Payload['voltage']);
                    }
                    if (array_key_exists('current', $Payload)) {
                        $this->SetValue('Current0', $Payload['current']);
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy0', $Payload['aenergy']['total'] / 1000);
                        }
                    }
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature0', $Payload['temperature']['tC']);
                        }
                    }
                }
                if (fnmatch('*/status/light:1', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State1', $Payload['output']);
                    }
                    if (array_key_exists('brightness', $Payload)) {
                        $this->SetValue('Brightness1', $Payload['brightness']);
                    }
                    if (array_key_exists('apower', $Payload)) {
                        $this->SetValue('Power1', $Payload['apower']);
                    }
                    if (array_key_exists('voltage', $Payload)) {
                        $this->SetValue('Voltage1', $Payload['voltage']);
                    }
                    if (array_key_exists('current', $Payload)) {
                        $this->SetValue('Current1', $Payload['current']);
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy1', $Payload['aenergy']['total'] / 1000);
                        }
                    }
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature1', $Payload['temperature']['tC']);
                        }
                    }
                }
            }
        }
    }

    public function SetBrightness(int $id, int $brightness, int $transition = 0, int $toggle_after = 0)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $id, 'brightness' => $brightness, 'transition' => $transition, 'toggle_after' => $toggle_after];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
    private function SwitchMode(int $id, bool $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $id, 'on' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}