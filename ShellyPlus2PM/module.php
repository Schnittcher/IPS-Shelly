<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlus2PM extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], 'relay', true, true, false],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Temperature0', 'Temperature0', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], 'relay', true, true, false],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], 'relay', false, true, false],
        ['TotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', [], 'relay', false, true, false],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', [], 'relay', false, true, false],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', [], 'relay', false, true, false],
        ['Temperature1', 'Temperature1', VARIABLETYPE_FLOAT, '~Temperature', [], 'relay', false, true, false],

        ['State', 'State', VARIABLETYPE_INTEGER, '~ShutterMoveStop', [], 'roller', true, true, false],
        ['RunningState', 'Running State', VARIABLETYPE_STRING, '', [], 'roller', true, true, false],
        ['CoverPosition', 'Position', VARIABLETYPE_INTEGER, '~Shutter', [], 'roller', true, true, false],

        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],

        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false],
        ['Temperature100', 'External Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature101', 'External Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature102', 'External Temperature 3', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature103', 'External Temperature 4', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Temperature104', 'External Temperature 5', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Humidity100', 'External Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true, false],
        ['Input100State', 'External Input State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input100Percent', 'External Input Percent 1', VARIABLETYPE_FLOAT, 'Shelly.Input.Percent', [], '', false, true, false],
        ['Input101State', 'External Input State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input101Percent', 'External Input Percent 2', VARIABLETYPE_FLOAT, 'Shelly.Input.Percent', [], '', false, true, false],
        ['Voltmeter100', 'External Voltmeter', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyString('DeviceType', '');
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State0':
                $this->SwitchMode(0, $Value);
                break;
            case 'State1':
                $this->SwitchMode(1, $Value);
                break;
            case 'State':
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
            case 'CoverPosition':
                $this->CoverPosition(0, $Value);
                break;
            }
    }
    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString, true);
            $this->SendDebug('JSON', $JSONString, 0);
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
                        for ($i = 0; $i <= 1; $i++) {
                            $inputIndex = 'input:' . $i;
                            if (array_key_exists($inputIndex, $Payload['params'])) {
                                $input = $Payload['params'][$inputIndex];
                                if (array_key_exists('state', $input)) {
                                    $this->SetValue('Input' . $i, $input['state']);
                                }
                            }
                        }

                        if (array_key_exists('switch:0', $Payload['params'])) {
                            $switch = $Payload['params']['switch:0'];
                            if (array_key_exists('output', $switch)) {
                                $this->SetValue('State0', $switch['output']);
                            }
                            if (array_key_exists('apower', $switch)) {
                                $this->SetValue('Power0', $switch['apower']);
                            }
                            if (array_key_exists('voltage', $switch)) {
                                $this->SetValue('Voltage0', $switch['voltage']);
                            }
                            if (array_key_exists('current', $switch)) {
                                $this->SetValue('Current0', $switch['current']);
                            }
                            if (array_key_exists('aenergy', $switch)) {
                                if (array_key_exists('total', $switch['aenergy'])) {
                                    $this->SetValue('TotalEnergy0', $switch['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('temperature', $switch)) {
                                if (array_key_exists('tC', $switch['temperature'])) {
                                    $this->SetValue('Temperature0', $switch['temperature']['tC']);
                                }
                            }
                        }
                        if (array_key_exists('switch:1', $Payload['params'])) {
                            $switch = $Payload['params']['switch:1'];
                            if (array_key_exists('output', $switch)) {
                                $this->SetValue('State1', $switch['output']);
                            }
                            if (array_key_exists('apower', $switch)) {
                                $this->SetValue('Power1', $switch['apower']);
                            }
                            if (array_key_exists('voltage', $switch)) {
                                $this->SetValue('Voltage1', $switch['voltage']);
                            }
                            if (array_key_exists('current', $switch)) {
                                $this->SetValue('Current1', $switch['current']);
                            }
                            if (array_key_exists('aenergy', $switch)) {
                                if (array_key_exists('total', $switch['aenergy'])) {
                                    $this->SetValue('TotalEnergy1', $switch['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('temperature', $switch)) {
                                if (array_key_exists('tC', $switch['temperature'])) {
                                    $this->SetValue('Temperature1', $switch['temperature']['tC']);
                                }
                            }
                        }
                        if (array_key_exists('cover:0', $Payload['params'])) {
                            $cover = $Payload['params']['cover:0'];
                            if (array_key_exists('state', $cover)) {
                                $this->SetValue('RunningState', $cover['state']);
                                switch ($cover['state']) {
                                    case 'stopped':
                                        $this->SetValue('State', 2);
                                        break;
                                    case 'opening':
                                        $this->SetValue('State', 0);
                                        break;
                                    case 'closing':
                                    case 'closed':
                                        $this->SetValue('State', 4);
                                        break;
                                    default:
                                        $this->SendDebug('Invalid Value for Cover', $cover['state'], 0);
                                        break;
                                }
                            }
                            if (array_key_exists('current_pos', $cover)) {
                                $this->SetValue('CoverPosition', $cover['current_pos']);
                            }
                        }
                        if (array_key_exists('apower', $Payload['params'])) {
                            $this->SetValue('Power0', $Payload['params']['apower']);
                        }
                        if (array_key_exists('voltage', $Payload['params'])) {
                            $this->SetValue('Voltage0', $Payload['params']['voltage']);
                        }
                        if (array_key_exists('current', $Payload['params'])) {
                            $this->SetValue('Current0', $Payload['params']['current']);
                        }
                        if (array_key_exists('aenergy', $Payload['params'])) {
                            if (array_key_exists('total', $Payload['params']['aenergy'])) {
                                $this->SetValue('TotalEnergy0', $Payload['params']['aenergy']['total'] / 1000);
                            }
                        }
                        if (array_key_exists('temperature', $Payload['params'])) {
                            if (array_key_exists('tC', $Payload['params']['temperature'])) {
                                $this->SetValue('Temperature0', $Payload['params']['temperature']['tC']);
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
                            if (array_key_exists('rh', $humidity)) {
                                $this->SetValue('Humidity100', $humidity['rh']);
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
                        if (array_key_exists('input:101', $Payload['params'])) {
                            $input = $Payload['params']['input:101'];
                            if (array_key_exists('state', $input)) {
                                $this->SetValue('Input101State', $input['state']);
                            }
                            if (array_key_exists('percent', $input)) {
                                $this->SetValue('Input101Percent', $input['percent']);
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
                if (fnmatch('*/status/switch:0', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State0', $Payload['output']);
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
                            $this->SetValue('Temperature0', $Payload['temperature']['tC']);
                        }
                    }
                }
                if (fnmatch('*/status/switch:1', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State1', $Payload['output']);
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
                if (fnmatch('*/status/cover:0', $Buffer['Topic'])) {
                    if (array_key_exists('current_pos', $Payload)) {
                        $this->SetValue('CoverPosition', $Payload['current_pos']);
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
                            $this->SetValue('Temperature0', $Payload['temperature']['tC']);
                        }
                    }
                }
            }
        }
    }

    public function ToggleAfter(int $switch, bool $value, int $toggle_after)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $switch, 'on' => $value, 'toggle_after' => $toggle_after];

        $this->sendMQTT($Topic, json_encode($Payload));
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
