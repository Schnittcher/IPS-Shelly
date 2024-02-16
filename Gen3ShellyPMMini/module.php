<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Gen3ShellyPMMini extends ShellyModule
{
    public static $Variables = [
        ['Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['TotalEnergy', 'Total Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Current', 'Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Voltage', 'Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Frequency ', 'Frequency', VARIABLETYPE_FLOAT, '~Hertz', [], '', false, true],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

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
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'][0];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
                        }
                        if (array_key_exists('pm1:0', $Payload['params'])) {
                            $service = $Payload['params']['pm1:0'];
                            if (array_key_exists('apower', $service)) {
                                $this->SetValue('Power', $service['apower']);
                            }
                            if (array_key_exists('voltage', $service)) {
                                $this->SetValue('Voltage', $service['voltage']);
                            }
                            if (array_key_exists('current', $service)) {
                                $this->SetValue('Current', $service['current']);
                            }
                            if (array_key_exists('freq', $service)) {
                                $this->SetValue('Frequency', $service['freq']);
                            }
                            if (array_key_exists('aenergy', $service)) {
                                if (array_key_exists('total', $service['aenergy'])) {
                                    $this->SetValue('TotalEnergy', $service['aenergy']['total'] / 1000);
                                }
                            }
                        }
                    }
                    if (fnmatch('*/status/pm1:0', $Buffer['Topic'])) {
                        if (array_key_exists('apower', $Payload)) {
                            $this->SetValue('Power', $Payload['apower']);
                        }
                        if (array_key_exists('voltage', $Payload)) {
                            $this->SetValue('Voltage', $Payload['voltage']);
                        }
                        if (array_key_exists('current', $Payload)) {
                            $this->SetValue('Current', $Payload['current']);
                        }
                        if (array_key_exists('freq', $Payload)) {
                            $this->SetValue('Frequency', $Payload['freq']);
                        }
                        if (array_key_exists('aenergy', $Payload)) {
                            if (array_key_exists('total', $Payload['aenergy'])) {
                                $this->SetValue('TotalEnergy', $Payload['aenergy']['total'] / 1000);
                            }
                        }
                    }
                }
            }
        }
    }
}
