<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Shelly2 extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', ['shelly2', 'shelly2.5'], 'relay', true, true, false],
        ['Shelly_State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', ['shelly2', 'shelly2.5'], 'relay', true, true, false],

        ['Shelly_Roller', 'Roller', VARIABLETYPE_INTEGER, '~ShutterMoveStop', ['shelly2', 'shelly2.5'], 'roller', true, true, false],
        ['Shelly_RollerPosition', 'Position', VARIABLETYPE_INTEGER, '~Shutter', ['shelly2', 'shelly2.5'], 'roller', true, true, false],
        ['Shelly_RollerStopReason', 'Stop Reason', VARIABLETYPE_STRING, '', ['shelly2', 'shelly2.5'], 'roller', false, true, false],

        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', ['shelly2'], '', false, true, false],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', ['shelly2'], '', false, true, false],

        ['Shelly_Power1', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', ['shelly2.5'], '', false, true, false],
        ['Shelly_Energy1', 'Energy 1', VARIABLETYPE_FLOAT, '~Electricity', ['shelly2.5'], '', false, true, false],
        ['Shelly_Power2', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', ['shelly2.5'], '', false, true, false],
        ['Shelly_Energy2', 'Energy 2', VARIABLETYPE_FLOAT, '~Electricity', ['shelly2.5'], '', false, true, false],
        ['Shelly_Temperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', ['shelly2.5'], '', false, true, false],
        ['Shelly_Overtemperature', 'Overtemperature', VARIABLETYPE_BOOLEAN, '', ['shelly2.5'], '', false, true, false],

        ['Shelly_Input', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Shelly_Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Shelly_Longpush', 'Longpush 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],
        ['Shelly_Longpush1', 'Longpush 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true, false],

        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString('DeviceType', '');
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode(0, $Value);
                break;
            case 'Shelly_State1':
                $this->SwitchMode(1, $Value);
                break;
            case 'Shelly_Roller':
                switch ($Value) {
                    case 0:
                        $this->MoveUp();
                        break;
                    case 2:
                        $this->Stop();
                        break;
                    case 4:
                        $this->MoveDown();
                        break;
                    default:
                        $this->SendDebug(__FUNCTION__ . 'Ident: Shelly_Roller', 'Invalid Value:' . $Value, 0);
                }
            break;
            case 'Shelly_RollerPosition':
                $this->SendDebug(__FUNCTION__ . ' Value Shelly_RollerPosition', $Value, 0);
                $this->Move($Value);
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/input/[01]', $Buffer->Topic)) {
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($Buffer->Payload) {
                        case 0:
                            switch ($input) {
                                case 0:
                                    $this->SetValue('Shelly_Input', 0);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_Input1', 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 1:
                            switch ($input) {
                                case 0:
                                    $this->SetValue('Shelly_Input', 1);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_Input1', 1);
                                    break;
                                default:
                                    break;
                            }
                            break;
                    }
                }

                if (fnmatch('*/longpush/[01]', $Buffer->Topic)) {
                    $longpush = $this->getChannelRelay($Buffer->Topic);
                    switch ($Buffer->Payload) {
                        case 0:
                            switch ($longpush) {
                                case 0:
                                    $this->SetValue('Shelly_Longpush', 0);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_Longpush1', 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 1:
                            switch ($longpush) {
                                case 0:
                                    $this->SetValue('Shelly_Longpush', 1);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_Longpush1', 1);
                                    break;
                                default:
                                    break;
                            }
                            break;
                    }
                }

                if (fnmatch('*/relay/[01]', $Buffer->Topic)) {
                    $relay = $this->getChannelRelay($Buffer->Topic);

                    //Power prüfen und in IPS setzen
                    switch ($Buffer->Payload) {
                        case 'off':
                            switch ($relay) {
                                case 0:
                                    $this->SetValue('Shelly_State', 0);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_State1', 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'on':
                            switch ($relay) {
                                case 0:
                                    $this->SetValue('Shelly_State', 1);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_State1', 1);
                                    break;
                                default:
                                    break;
                            }
                            break;
                    }
                }
                if (fnmatch('*/roller/stop_reason', $Buffer->Topic)) {
                    $this->SetValue('Shelly_RollerStopReason', $Buffer->Payload);
                }
                if (fnmatch('*/roller/0', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'open':
                            $this->SetValue('Shelly_Roller', 0);
                            break;
                        case 'stop':
                            $this->SetValue('Shelly_Roller', 2);
                            break;
                        case 'close':
                            $this->SetValue('Shelly_Roller', 4);
                            break;
                        default:
                            if (!fnmatch('*/roller/0/pos*', $Buffer->Topic)) {
                                $this->SendDebug(__FUNCTION__ . ' Roller', 'Invalid Value: ' . $Buffer->Payload, 0);
                            }
                            break;
                    }
                }
                if (fnmatch('*/roller/0/pos*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_RollerPosition', intval($Buffer->Payload));
                }
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/overtemperature', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Overtemperature', boolval($Buffer->Payload));
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Reachable', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Reachable', false);
                            $this->zeroingValues();
                            break;
                    }
                }
                switch ($this->ReadPropertyString('Device')) {
                    case 'shelly2':
                        if (fnmatch('*/relay/power*', $Buffer->Topic)) {
                            $this->SetValue('Shelly_Power', $Buffer->Payload);
                        }
                        if (fnmatch('*/relay/energy*', $Buffer->Topic)) {
                            $this->SetValue('Shelly_Energy', $Buffer->Payload / 60000);
                        }
                        break;
                    case 'shelly2.5':
                        if (fnmatch('*/0/power*', $Buffer->Topic)) {
                            $this->SetValue('Shelly_Power1', $Buffer->Payload);
                        }
                        if (fnmatch('*/0/energy*', $Buffer->Topic)) {
                            $this->SetValue('Shelly_Energy1', $Buffer->Payload / 60000);
                        }
                        if (fnmatch('*/1/power*', $Buffer->Topic)) {
                            $this->SetValue('Shelly_Power2', $Buffer->Payload);
                        }
                        if (fnmatch('*/1/energy*', $Buffer->Topic)) {
                            $this->SetValue('Shelly_Energy2', $Buffer->Payload / 60000);
                        }
                        break;
                }
            }
        }
    }

    private function MoveDown()
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Payload = 'close';
        $this->sendMQTT($Topic, $Payload);
    }

    private function MoveUp()
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Payload = 'open';
        $this->sendMQTT($Topic, $Payload);
    }

    private function Move($position)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command/pos';
        $Payload = strval($position);
        $this->sendMQTT($Topic, $Payload);
    }

    private function Stop()
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Payload = 'stop';
        $this->sendMQTT($Topic, $Payload);
    }

    private function SwitchMode(int $relay, bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/' . $relay . '/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}
