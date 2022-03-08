<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyPlus1 extends IPSModule
{
    use Shelly;
    use VariableProfileHelper;
    use MQTTHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('DeviceType', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        switch ($this->ReadPropertyString('DeviceType')) {
            case 'relay':
                $this->SendDebug(__FUNCTION__ . ' Device Type: ', ' Relay', 0);
                $this->RegisterVariableBoolean('State0', $this->Translate('State'), '~Switch');
                $this->EnableAction('State0');
                $this->RegisterVariableFloat('Power0', $this->Translate('Power') . ' 1', '~Watt.3680');
                $this->RegisterVariableFloat('TotalEnergy0', $this->Translate('Total Energy') . ' 1', '~Electricity');
                $this->RegisterVariableFloat('Current0', $this->Translate('Current') . ' 1', '~Ampere');
                $this->RegisterVariableFloat('Voltage0', $this->Translate('Voltage') . ' 1', '~Volt.230');

                $this->RegisterVariableBoolean('State1', $this->Translate('State') . ' 2', '~Switch');
                $this->EnableAction('State1');
                $this->RegisterVariableFloat('Power1', $this->Translate('Power') . ' 2', '~Watt.3680');
                $this->RegisterVariableFloat('TotalEnergy1', $this->Translate('Total Energy') . ' 1', '~Electricity');
                $this->RegisterVariableFloat('Current1', $this->Translate('Current') . ' 2', '~Ampere');
                $this->RegisterVariableFloat('Voltage1', $this->Translate('Voltage') . ' 2', '~Volt.230');
                break;
            case 'roller':
                $this->SendDebug(__FUNCTION__ . ' Device Type: ', ' Roller', 0);
                $this->RegisterVariableInteger('Cover', $this->Translate('Roller'), '~ShutterMoveStop');
                $this->EnableAction('Cover');
                $this->RegisterVariableInteger('Shelly_RollerPosition', $this->Translate('Position'), '~Shutter');
                $this->EnableAction('CoverPosition');
                break;
            default:
                $this->SendDebug(__FUNCTION__ . ' Device Type: ', 'No Device Type', 0);
        }
        $this->RegisterStringVariable('EventComponent', $this->Translate('Event Component'), 8);
        $this->RegisterStringVariable('Event', $this->Translate('Event'), 9);

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
        $this->RegisterVariableBoolean('Reachable', $this->Translate('Reachable'), 'Shelly.Reachable', 150);
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
                        if (array_key_exists('events', $Payload)) {
                            $events = $Payload['events'];
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
                                        $this->SetValue('Cover', 4);
                                        break;
                                    case 'opening':
                                        $this->SetValue('Cover', 0);
                                        break;
                                    case 'closing':
                                        $this->SetValue('Cover', 2);
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
