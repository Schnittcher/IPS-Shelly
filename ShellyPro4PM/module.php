<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPro4PM extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['TotalEnergy0', 'Total consumption 1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Powerfactor0', 'Powerfactor 1', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Overtemp0', 'Overtemp 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overpower0', 'Overpower 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overvoltage0', 'Overvoltage 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['TotalEnergy1', 'Total consumption 2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Powerfactor1', 'Powerfactor 2', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Overtemp1', 'Overtemp 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overpower1', 'Overpower 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overvoltage1', 'Overvoltage 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],

        ['State2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Power2', 'Power 3', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['TotalEnergy2', 'Total consumption 3', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Current2', 'Current 3', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Voltage2', 'Voltage 3', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Powerfactor2', 'Powerfactor 3', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Overtemp2', 'Overtemp 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overpower2', 'Overpower 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overvoltage2', 'Overvoltage 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],

        ['State3', 'State 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Power3', 'Power 4', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['TotalEnergy3', 'Total consumption 4', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Current3', 'Current 4', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Voltage3', 'Voltage 4', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Powerfactor3', 'Powerfactor 4', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Overtemp3', 'Overtemp 4', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overpower3', 'Overpower 4', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Overvoltage3', 'Overvoltage 4', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],

        ['DeviceTemperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true],
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
                            $events = $Payload['params']['events'][0];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
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
