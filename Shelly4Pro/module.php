<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Shelly4Pro extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],

        ['Shelly_State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Shelly_Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['Shelly_Energy1', 'Energy 2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],

        ['Shelly_State2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Shelly_Power2', 'Power 3', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['Shelly_Energy2', 'Energy 3', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],

        ['Shelly_State3', 'State 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Shelly_Power3', 'Power 4', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['Shelly_Energy3', 'Energy 4', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],

        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode(0, $Value);
                break;
            case 'Shelly_State1':
                $this->SwitchMode(1, $Value);
                break;
            case 'Shelly_State2':
                $this->SwitchMode(2, $Value);
                break;
            case 'Shelly_State3':
                $this->SwitchMode(3, $Value);
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

            //Power Variable prüfen
            if (property_exists($Buffer, 'Topic')) {
                //Ist es ein Relay?
                if (fnmatch('*/relay/[0123]', $Buffer->Topic)) {
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
                                case 2:
                                    $this->SetValue('Shelly_State2', 0);
                                    break;
                                case 3:
                                    $this->SetValue('Shelly_State3', 0);
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
                                case 2:
                                    $this->SetValue('Shelly_State2', 1);
                                    break;
                                case 3:
                                    $this->SetValue('Shelly_State3', 1);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'overpower':
                            switch ($relay) {
                                case 0:
                                    //TODO
                                    break;
                                case 1:
                                    //TODO
                                    break;
                                case 2:
                                    //TODO
                                    break;
                                case 3:
                                    //TODO
                                    break;
                                default:
                                    break;
                            }
                            break;
                    }
                }
                if (fnmatch('*/relay/[0123]/power*', $Buffer->Topic)) {
                    $ShellyTopic = explode('/', $Buffer->Topic);
                    $Key = count($ShellyTopic) - 2;
                    $relay = $ShellyTopic[$Key];

                    switch ($relay) {
                        case 0:
                            $this->SetValue('Shelly_Power', $Buffer->Payload);
                            break;
                        case 1:
                            $this->SetValue('Shelly_Power1', $Buffer->Payload);
                            break;
                        case 2:
                            $this->SetValue('Shelly_Power2', $Buffer->Payload);
                            break;
                        case 3:
                            $this->SetValue('Shelly_Power3', $Buffer->Payload);
                            break;
                        default:
                            $this->SendDebug('Relay Power', 'Undefined Relay: ' . $relay, 0);
                            break;
                    }
                }
                if (fnmatch('*/relay/[0123]/energy*', $Buffer->Topic)) {
                    $ShellyTopic = explode('/', $Buffer->Topic);
                    $Key = count($ShellyTopic) - 2;
                    $relay = $ShellyTopic[$Key];

                    switch ($relay) {
                        case 0:
                            $this->SetValue('Shelly_Energy', $Buffer->Payload / 60000);
                            break;
                        case 1:
                            $this->SetValue('Shelly_Energy1', $Buffer->Payload / 60000);
                            break;
                        case 2:
                            $this->SetValue('Shelly_Energy2', $Buffer->Payload / 60000);
                            break;
                        case 3:
                            $this->SetValue('Shelly_Energy3', $Buffer->Payload / 60000);
                            break;
                        default:
                            $this->SendDebug('Relay Energy', 'Undefined Relay: ' . $relay, 0);
                            break;
                    }
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
            }
        }
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
