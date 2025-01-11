<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Gen3ShellyDALIDimmer extends ShellyModule
{
    public static $Variables = [
        ['State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true, false],
        ['Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy', 'Total Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current', 'Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage', 'Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Overtemp', 'Overtemp', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower', 'Overpower', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage', 'Overvoltage', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['DeviceTemperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        //ggf. noch timer_stared_at, timer_duration
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State':
                $this->SwitchMode(0, $Value);
                break;
            case 'Brightness':
                $this->SetBrightness(0, $Value);
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
                                $this->SetValue('State', $service['output']);
                            }
                            if (array_key_exists('brightness', $service)) {
                                $this->SetValue('Brightness', $service['brightness']);
                            }
                            if (array_key_exists('apower', $service)) {
                                $this->SetValue('Power', $service['apower']);
                            }
                            if (array_key_exists('voltage', $service)) {
                                $this->SetValue('Voltage', $service['voltage']);
                            }
                            if (array_key_exists('current', $service)) {
                                $this->SetValue('Current', $service['current']);
                            }
                            if (array_key_exists('aenergy', $service)) {
                                if (array_key_exists('total', $service['aenergy'])) {
                                    $this->SetValue('TotalEnergy', $service['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('errors', $service)) {
                                $this->SetValue('Overtemp', false);
                                $this->SetValue('Overpower', false);
                                $this->SetValue('Overvoltage', false);
                                $errors = '';
                                foreach ($service['errors'] as $key => $error) {
                                    switch ($error) {
                                            case 'overtemp':
                                                $this->SetValue('Overtemp', true);
                                                break;
                                            case 'overpower':
                                                $this->SetValue('Overpower', true);
                                                break;
                                            case 'overvoltage':
                                                $this->SetValue('Overvoltage', true);
                                                break;
                                            default:
                                                $this->LogMessage('Missing Variable for Error State "' . $error . '"', KL_ERROR);
                                                break;
                                        }
                                }
                            }
                            if (array_key_exists('temperature', $Payload)) {
                                if (array_key_exists('tC', $Payload['temperature'])) {
                                    $this->SetValue('DeviceTemperature', $Payload['temperature']['tC']);
                                }
                            }
                        }
                    }
                }
                if (fnmatch('*/status/light:0', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State', $Payload['output']);
                    }
                    if (array_key_exists('brightness', $Payload)) {
                        $this->SetValue('Brightness', $Payload['brightness']);
                    }
                    if (array_key_exists('apower', $Payload)) {
                        $this->SetValue('Power', $Payload['apower']);
                    }
                    if (array_key_exists('voltage', $Payload)) {
                        $this->SetValue('Voltage', $Payload['voltage']);
                    }
                    if (array_key_exists('current', $Payload)) {
                        $this->SetValue('Current', $Payload['current']);
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy', $Payload['aenergy']['total'] / 1000);
                        }
                    }
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature', $Payload['temperature']['tC']);
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
        $Payload['method'] = 'Light.Set';
        $Payload['params'] = ['id' => $id, 'brightness' => $brightness, 'transition' => $transition];
        if ($toggle_after > 0) {
            $Payload['params'] = ['id' => $id, 'brightness' => $brightness, 'transition' => $transition, 'toggle_after' => $transition];
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }
    private function SwitchMode(int $id, bool $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Light.Set';
        $Payload['params'] = ['id' => $id, 'on' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}