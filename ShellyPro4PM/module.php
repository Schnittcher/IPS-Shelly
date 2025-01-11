<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPro4PM extends ShellyModule
{
    use ShellyGen2Plus;

    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy0', 'Total consumption 1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Powerfactor0', 'Powerfactor 1', VARIABLETYPE_FLOAT, '', [], '', false, true, false],
        ['Overtemp0', 'Overtemp 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower0', 'Overpower 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage0', 'Overvoltage 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy1', 'Total consumption 2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Powerfactor1', 'Powerfactor 2', VARIABLETYPE_FLOAT, '', [], '', false, true, false],
        ['Overtemp1', 'Overtemp 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower1', 'Overpower 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage1', 'Overvoltage 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],

        ['State2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Power2', 'Power 3', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy2', 'Total consumption 3', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current2', 'Current 3', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage2', 'Voltage 3', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Powerfactor2', 'Powerfactor 3', VARIABLETYPE_FLOAT, '', [], '', false, true, false],
        ['Overtemp2', 'Overtemp 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower2', 'Overpower 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage2', 'Overvoltage 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Input2', 'Input 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],

        ['State3', 'State 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Power3', 'Power 4', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['TotalEnergy3', 'Total consumption 4', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Current3', 'Current 4', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage3', 'Voltage 4', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Powerfactor3', 'Powerfactor 4', VARIABLETYPE_FLOAT, '', [], '', false, true, false],
        ['Overtemp3', 'Overtemp 4', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower3', 'Overpower 4', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage3', 'Overvoltage 4', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Input3', 'Input 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],

        ['DeviceTemperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
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
            case 'State2':
                $this->SwitchMode(2, $Value);
                break;
            case 'State3':
                $this->SwitchMode(3, $Value);
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

                        for ($i = 0; $i <= 3; $i++) {
                            $inputIndex = 'input:' . $i;
                            if (array_key_exists($inputIndex, $Payload['params'])) {
                                $input = $Payload['params'][$inputIndex];
                                if (array_key_exists('state', $input)) {
                                    $this->SetValue('Input' . $i, $input['state']);
                                }
                            }
                        }

                        for ($i = 0; $i <= 3; $i++) {
                            $switchIndex = 'switch:' . $i;
                            if (array_key_exists($switchIndex, $Payload['params'])) {
                                $switch = $Payload['params'][$switchIndex];
                                if (array_key_exists('output', $switch)) {
                                    $this->SetValue('State' . $i, $switch['output']);
                                }
                                if (array_key_exists('apower', $switch)) {
                                    $this->SetValue('Power' . $i, $switch['apower']);
                                }
                                if (array_key_exists('voltage', $switch)) {
                                    $this->SetValue('Voltage' . $i, $switch['voltage']);
                                }
                                if (array_key_exists('pf', $switch)) {
                                    $this->SetValue('Powerfactor' . $i, floatval($switch['pf']));
                                }
                                if (array_key_exists('current', $switch)) {
                                    $this->SetValue('Current' . $i, $switch['current']);
                                }
                                if (array_key_exists('aenergy', $switch)) {
                                    if (array_key_exists('total', $switch['aenergy'])) {
                                        $this->SetValue('TotalEnergy' . $i, $switch['aenergy']['total'] / 1000);
                                    }
                                }
                                if (array_key_exists('errors', $switch)) {
                                    $this->SetValue('Overtemp' . $i, false);
                                    $this->SetValue('Overpower' . $i, false);
                                    $this->SetValue('Overvoltage' . $i, false);
                                    $errors = '';
                                    foreach ($switch['errors'] as $key => $error) {
                                        switch ($error) {
                                            case 'overtemp':
                                                $this->SetValue('Overtemp' . $i, true);
                                                break;
                                            case 'overpower':
                                                $this->SetValue('Overpower' . $i, true);
                                                break;
                                            case 'Overvoltage':
                                                $this->SetValue('Overvoltage' . $i, true);
                                                break;
                                            default:
                                                $this->LogMessage('Missing Variable for Error State "' . $error . '"', KL_ERROR);
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //Temperatur ist immer vorhanden und soltle immer der selbe Wert sein.
                if (fnmatch('*/status/*', $Buffer['Topic'])) {
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature', $Payload['temperature']['tC']);
                        }
                    }
                }

                if (fnmatch('*/status/input:*', $Buffer['Topic'])) {
                    if (array_key_exists('state', $Payload)) {
                        $this->SetValue('Input' . $Payload['id'], $Payload['state']);
                    }
                }
                if (fnmatch('*/status/switch:*', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State' . $Payload['id'], $Payload['output']);
                    }
                    if (array_key_exists('apower', $Payload)) {
                        $this->SetValue('Power' . $Payload['id'], $Payload['apower']);
                    }
                    if (array_key_exists('voltage', $Payload)) {
                        $this->SetValue('Voltage' . $Payload['id'], $Payload['voltage']);
                    }
                    if (array_key_exists('pf', $Payload)) {
                        $this->SetValue('Powerfactor' . $Payload['id'], floatval($Payload['pf']));
                    }
                    if (array_key_exists('current', $Payload)) {
                        $this->SetValue('Current' . $Payload['id'], $Payload['current']);
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy' . $Payload['id'], $Payload['aenergy']['total'] / 1000);
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
}
