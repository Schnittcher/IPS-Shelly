<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyProDualCoverPM extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_INTEGER, '~ShutterMoveStop', [], 'roller', true, true, false],
        ['RunningState0', 'Running State 1', VARIABLETYPE_STRING, '', [], 'roller', true, true, false],
        ['CoverPosition0', 'Position 1', VARIABLETYPE_INTEGER, '~Shutter', [], 'roller', true, true, false],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Errors0', 'Errors 1', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['DeviceTemperature0', 'Device Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],

        ['State1', 'State 2', VARIABLETYPE_INTEGER, '~ShutterMoveStop', [], 'roller', true, true, false],
        ['RunningState1', 'Running State 2', VARIABLETYPE_STRING, '', [], 'roller', true, true, false],
        ['CoverPosition1', 'Position 2', VARIABLETYPE_INTEGER, '~Shutter', [], 'roller', true, true, false],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Errors1', 'Errors 2', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['DeviceTemperature1', 'Device Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],

        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true,  false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true,  false],
        ['Input2', 'Input 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true,  false],
        ['Input3', 'Input 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true,  false],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State0':
                switch ($Value) {
                    case 0:
                        $this->CoverOpen(0);
                        break;
                    case 2:
                        $this->CoverStop(0);
                        break;
                    case 4:
                        $this->CoverClose(0);
                        break;
                    default:
                        $this->SendDebug('Invalid Value :: Request Action Cover', $Value, 0);
                        break;
                }
                break;
            case 'State1':
                switch ($Value) {
                    case 0:
                        $this->CoverOpen(1);
                        break;
                    case 2:
                        $this->CoverStop(1);
                        break;
                    case 4:
                        $this->CoverClose(1);
                        break;
                    default:
                        $this->SendDebug('Invalid Value :: Request Action Cover', $Value, 0);
                        break;
                }
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
                        if (array_key_exists('input:2', $Payload['params'])) {
                            if (array_key_exists('state', $Payload['params']['input:2'])) {
                                $this->SetValue('Input2', $Payload['params']['input:2']['state']);
                            }
                        }
                        if (array_key_exists('input:3', $Payload['params'])) {
                            if (array_key_exists('state', $Payload['params']['input:3'])) {
                                $this->SetValue('Input3', $Payload['params']['input:3']['state']);
                            }
                        }

                        if (array_key_exists('cover:0', $Payload['params'])) {
                            $service = $Payload['params']['cover:0'];
                            if (array_key_exists('current_pos', $service)) {
                                $this->SetValue('CoverPosition0', $service['current_pos']);
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
                            if (array_key_exists('temperature', $service)) {
                                if (array_key_exists('tC', $service['temperature'])) {
                                    $this->SetValue('Temperature0', $service['temperature']['tC']);
                                }
                            }
                            if (array_key_exists('errors', $service)) {
                                $errors = implode(',', $service['errors']);
                                $this->SetValue('Errors1', $errors);
                            }
                        }
                        if (array_key_exists('cover:1', $Payload['params'])) {
                            $service = $Payload['params']['cover:1'];
                            if (array_key_exists('current_pos', $service)) {
                                $this->SetValue('CoverPosition1', $service['current_pos']);
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
                            if (array_key_exists('temperature', $service)) {
                                if (array_key_exists('tC', $service['temperature'])) {
                                    $this->SetValue('Temperature1', $service['temperature']['tC']);
                                }
                            }
                            if (array_key_exists('errors', $service)) {
                                $errors = implode(',', $service['errors']);
                                $this->SetValue('Errors1', $errors);
                            }
                        }
                    }
                }
                if (fnmatch('*/status/cover:1', $Buffer['Topic'])) {
                    if (array_key_exists('current_pos', $Payload)) {
                        $this->SetValue('CoverPosition1', $Payload['current_pos']);
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
                            $this->SetValue('Temperature1', $Payload['temperature']['tC']);
                        }
                    }
                }
            }
        }
    }

    private function CoverOpen(int $cover)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.Open';
        $Payload['params'] = ['id' => $cover];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverClose(int $cover)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.Close';
        $Payload['params'] = ['id' => $cover];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverStop(int $cover)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.Stop';
        $Payload['params'] = ['id' => $cover];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverPosition(int $cover, int $position)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.GoToPosition';
        $Payload['params'] = ['id' => $cover, 'pos' => $position];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}