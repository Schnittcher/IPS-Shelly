<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPro3 extends ShellyModule
{
    use ShellyGen2Plus;

    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Overtemp0', 'Overtemp 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower0', 'Overpower 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage0', 'Overvoltage 1', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Overtemp1', 'Overtemp 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower1', 'Overpower 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage1', 'Overvoltage 2', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],

        ['State2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Overtemp2', 'Overtemp 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overpower2', 'Overpower 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Overvoltage2', 'Overvoltage 3', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],

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
                        for ($i = 0; $i <= 2; $i++) {
                            $switchIndex = 'switch:' . $i;
                            if (array_key_exists($switchIndex, $Payload['params'])) {
                                $switch = $Payload['params'][$switchIndex];
                                if (array_key_exists('output', $switch)) {
                                    $this->SetValue('State' . $i, $switch['output']);
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
                //Temperatur ist immer vorhanden und sollte immer der selbe Wert sein.
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
