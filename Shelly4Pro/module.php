<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Shelly4Pro extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy1', 'Energy 2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_State2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Power2', 'Power 3', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy2', 'Energy 3', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_State3', 'State 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Power3', 'Power 4', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy3', 'Energy 4', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
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
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);

            switch ($data->DataID) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data->Buffer);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
            }

            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);

            //Power Variable prüfen
            if (property_exists($Buffer, 'Topic')) {
                //Ist es ein Relay?
                if (fnmatch('*/relay/[0123]', $Buffer->Topic)) {
                    $this->SendDebug('State Payload', $Buffer->Payload, 0);
                    $relay = $this->getChannelRelay($Buffer->Topic);
                    $this->SendDebug(__FUNCTION__ . ' Relay', $relay, 0);

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
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
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
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
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
                    $this->SendDebug('Online Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Reachable', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Reachable', false);
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
