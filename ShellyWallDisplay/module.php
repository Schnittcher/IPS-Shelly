<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyWallDisplay extends ShellyModule
{
    public static $Variables = [
        ['State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Humidity', 'Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true, false],
        ['Illuminance', 'Illuminance', VARIABLETYPE_FLOAT, '~Illumination.F', [], '', false, true, false],
        ['Input0', 'Input', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false],
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State':
                $this->SwitchMode(0, $Value);
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
                        if (array_key_exists('temperature:0', $Payload['params'])) {
                            if (array_key_exists('tC', $Payload['params']['temperature:0'])) {
                                $this->SetValue('Temperature', $Payload['params']['temperature:0']['tC']);
                            }
                        }
                        if (array_key_exists('humidity:0', $Payload['params'])) {
                            if (array_key_exists('rh', $Payload['params']['humidity:0'])) {
                                $this->SetValue('Humidity', $Payload['params']['humidity:0']['rh']);
                            }
                        }
                        if (array_key_exists('illuminance:0', $Payload['params'])) {
                            if (array_key_exists('lux', $Payload['params']['illuminance:0'])) {
                                $this->SetValue('Illuminance', $Payload['params']['illuminance:0']['lux']);
                            }
                        }

                        if (array_key_exists('switch:0', $Payload['params'])) {
                            $switch = $Payload['params']['switch:0'];
                            if (array_key_exists('output', $switch)) {
                                $this->SetValue('State', $switch['output']);
                            }
                            if (array_key_exists('errors', $switch)) {
                                $this->SetValue('Overtemp', false);
                                $this->SetValue('Overpower', false);
                                $this->SetValue('Overvoltage', false);
                                $errors = '';
                                foreach ($switch['errors'] as $key => $error) {
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
                        }
                    }
                    if (fnmatch('*/status/switch:0', $Buffer['Topic'])) {
                        if (array_key_exists('output', $Payload)) {
                            $this->SetValue('State', $Payload['output']);
                        }
                    }
                    if (fnmatch('*/status/illuminance:0', $Buffer['Topic'])) {
                        if (array_key_exists('lux', $Payload)) {
                            $this->SetValue('Illuminance', $Payload['lux']);
                        }
                    }
                    if (fnmatch('*/status/temperature:0', $Buffer['Topic'])) {
                        if (array_key_exists('lux', $Payload)) {
                            $this->SetValue('Temperature', $Payload['tC']);
                        }
                    }
                    if (fnmatch('*/status/humidity:0', $Buffer['Topic'])) {
                        if (array_key_exists('rh', $Payload)) {
                            $this->SetValue('Humidity', $Payload['rh']);
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
