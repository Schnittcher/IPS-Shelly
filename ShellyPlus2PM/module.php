<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlus2PM extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], 'relay', true, true],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', [], 'relay', false, true],
        ['TotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', [], 'relay', false, true],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', [], 'relay', false, true],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', [], 'relay', false, true],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], 'relay', true, true],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], 'relay', false, true],
        ['TotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', [], 'relay', false, true],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', [], 'relay', false, true],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', [], 'relay', false, true],

        ['Cover', 'Roller', VARIABLETYPE_BOOLEAN, '~ShutterMoveStop', [], 'roller', true, true],
        ['CoverPosition', 'Position', VARIABLETYPE_BOOLEAN, '~Shutter', [], 'roller', true, true],

        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyString('DeviceType', '-');
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
            case 'Cover':
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
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString, true);

            switch ($data['DataID']) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data['Buffer']);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
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
                        }
                        if (array_key_exists('cover:0', $Payload['params'])) {
                            $cover = $Payload['params']['cover:0'];
                            if (array_key_exists('state', $cover)) {
                                switch ($cover['state']) {
                                    case 'stopped':
                                        $this->SetValue('Cover', 2);
                                        break;
                                    case 'opening':
                                        $this->SetValue('Cover', 0);
                                        break;
                                    case 'closing':
                                        $this->SetValue('Cover', 4);
                                        break;
                                    default:
                                        $this->SendDebug('Invalid Value for Cover', $cover['state'], 0);
                                        break;
                                }
                            }
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

    private function CoverOpen(int $cover)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.Open';
        $Payload['params'] = ['id' => $switch];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverClose(int $cover)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.Close';
        $Payload['params'] = ['id' => $switch];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverStop(int $cover)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.Stop';
        $Payload['params'] = ['id' => $switch];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverPosition(int $cover, int $position)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.GoToPosition';
        $Payload['params'] = ['id' => $switch, 'pos' => $position];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}
