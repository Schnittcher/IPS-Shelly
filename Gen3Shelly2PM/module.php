<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Gen3Shelly2PM extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], 'relay', true, true, false],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Frequency0', 'Frequency 1', VARIABLETYPE_FLOAT, '~Hertz', [], '', false, true, false],
        ['Overtemp0', 'Overtemp 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower0', 'Overpower 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage0', 'Overvoltage 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['DeviceTemperature0', 'Device Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], 'relay', true, true, false],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], 'relay', false, true, false],
        ['TotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', [], 'relay', false, true, false],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', [], 'relay', false, true, false],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', [], 'relay', false, true, false],
        ['Frequency1', 'Frequency 2', VARIABLETYPE_FLOAT, '~Hertz', [], 'relay', false, true, false],
        ['Overtemp1', 'Overtemp 2', VARIABLETYPE_BOOLEAN, '~Alert', [], 'relay', false, true, false],
        ['Overpower1', 'Overpower 2', VARIABLETYPE_BOOLEAN, '~Alert', [], 'relay', false, true, false],
        ['Overvoltage1', 'Overvoltage 2', VARIABLETYPE_BOOLEAN, '~Alert', [], 'relay', false, true, false],
        ['DeviceTemperature1', 'Device Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], 'relay', false, true, false],

        ['State', 'State', VARIABLETYPE_INTEGER, '~ShutterMoveStop', [], 'roller', true, true, false],
        ['RunningState', 'Running State', VARIABLETYPE_STRING, '', [], 'roller', true, true, false],
        ['CoverPosition', 'Position', VARIABLETYPE_INTEGER, '~Shutter', [], 'roller', true, true, false],
        ['SlatPosition', 'Slat Position', VARIABLETYPE_INTEGER, '~Shutter', [], 'roller', true, true, false],

        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],

        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
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
            case 'SlatPosition':
                $this->SLatPosition(0, $Value);
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
                            if (array_key_exists('freq', $Payload)) {
                                $this->SetValue('Frequency0', $Payload['freq']);
                            }
                            if (array_key_exists('temperature', $switch)) {
                                if (array_key_exists('tC', $switch['temperature'])) {
                                    $this->SetValue('DeviceTemperature0', $switch['temperature']['tC']);
                                }
                            }
                            if (array_key_exists('aenergy', $switch)) {
                                if (array_key_exists('total', $switch['aenergy'])) {
                                    $this->SetValue('TotalEnergy0', $switch['aenergy']['total'] / 1000);
                                }
                            }

                            if (array_key_exists('errors', $switch)) {
                                $this->SetValue('Overtemp0', false);
                                $this->SetValue('Overpower0', false);
                                $this->SetValue('Overvoltage0', false);
                                $errors = '';
                                foreach ($switch['errors'] as $key => $error) {
                                    switch ($error) {
                                            case 'overtemp':
                                                $this->SetValue('Overtemp0', true);
                                                break;
                                            case 'overpower':
                                                $this->SetValue('Overpower0', true);
                                                break;
                                            case 'overvoltage':
                                                $this->SetValue('Overvoltage0', true);
                                                break;
                                            default:
                                                $this->LogMessage('Missing Variable for Error State "' . $error . '"', KL_ERROR);
                                                break;
                                        }
                                }
                            }
                        }
                    }
                    if (array_key_exists('switch:1', $Payload['params'])) {
                        $switch = $Payload['params']['switch:0'];
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
                        if (array_key_exists('freq', $Payload)) {
                            $this->SetValue('Frequency1', $Payload['freq']);
                        }
                        if (array_key_exists('temperature', $switch)) {
                            if (array_key_exists('tC', $switch['temperature'])) {
                                $this->SetValue('DeviceTemperature1', $switch['temperature']['tC']);
                            }
                        }
                        if (array_key_exists('aenergy', $switch)) {
                            if (array_key_exists('total', $switch['aenergy'])) {
                                $this->SetValue('TotalEnergy1', $switch['aenergy']['total'] / 1000);
                            }
                        }

                        if (array_key_exists('errors', $switch)) {
                            $this->SetValue('Overtemp1', false);
                            $this->SetValue('Overpower1', false);
                            $this->SetValue('Overvoltage1', false);
                            $errors = '';
                            foreach ($switch['errors'] as $key => $error) {
                                switch ($error) {
                                        case 'overtemp':
                                            $this->SetValue('Overtemp1', true);
                                            break;
                                        case 'overpower':
                                            $this->SetValue('Overpower1', true);
                                            break;
                                        case 'overvoltage':
                                            $this->SetValue('Overvoltage1', true);
                                            break;
                                        default:
                                            $this->LogMessage('Missing Variable for Error State "' . $error . '"', KL_ERROR);
                                            break;
                                    }
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
                        if (array_key_exists('slat_pos', $cover)) {
                            $this->SetValue('SlatPosition', $cover['slat_pos']);
                        }
                        if (array_key_exists('apower', $cover)) {
                            $this->SetValue('Power0', $cover['apower']);
                        }
                        if (array_key_exists('voltage', $cover)) {
                            $this->SetValue('Voltage0', $cover['voltage']);
                        }
                        if (array_key_exists('current', $cover)) {
                            $this->SetValue('Current0', $cover['current']);
                        }
                        if (array_key_exists('aenergy', $cover)) {
                            if (array_key_exists('total', $cover['aenergy'])) {
                                $this->SetValue('TotalEnergy0', $cover['aenergy']['total'] / 1000);
                            }
                        }
                        if (array_key_exists('temperature', $cover)) {
                            if (array_key_exists('tC', $cover['temperature'])) {
                                $this->SetValue('Temperature0', $cover['temperature']['tC']);
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
                    if (array_key_exists('freq', $Payload)) {
                        $this->SetValue('Frequency0', $Payload['freq']);
                    }
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature0', $Payload['temperature']['tC']);
                        }
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy0', $Payload['aenergy']['total'] / 1000);
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
                    if (array_key_exists('freq', $Payload)) {
                        $this->SetValue('Frequency1', $Payload['freq']);
                    }
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature1', $Payload['temperature']['tC']);
                        }
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy1', $Payload['aenergy']['total'] / 1000);
                        }
                    }
                }
                if (fnmatch('*/status/cover:0', $Buffer['Topic'])) {
                    if (array_key_exists('current_pos', $Payload)) {
                        $this->SetValue('CoverPosition', $Payload['current_pos']);
                    }
                    if (array_key_exists('slat_pos', $Payload)) {
                        $this->SetValue('SlatPosition', $Payload['slat_pos']);
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

    private function SlatPosition(int $cover, int $position)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.GoToPosition';
        $Payload['params'] = ['id' => $cover, 'slat_pos' => $position];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}