<?php

require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_Shelly4Pro extends IPSModule
{
    use ShellyRelayAction;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{EE0D345A-CF31-428A-A613-33CE98E752DD}');

        $this->RegisterPropertyString('MQTTTopic', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{EE0D345A-CF31-428A-A613-33CE98E752DD}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        $this->SendDebug(__FUNCTION__ .' Device Type: ',' Relay',0);
        $this->RegisterVariableBoolean('Shelly_State','State','~Switch');
        $this->EnableAction('Shelly_State');
        $this->RegisterVariableFloat('Shelly_Power','Power','');
        $this->RegisterVariableFloat('Shelly_Energy','Energy','');

        $this->RegisterVariableBoolean('Shelly_State1','State 2','~Switch');
        $this->EnableAction('Shelly_State1');
        $this->RegisterVariableFloat('Shelly_Power1','Power 2','');
        $this->RegisterVariableFloat('Shelly_Energy1','Energy 2','');

        $this->RegisterVariableBoolean('Shelly_State2','State 3','~Switch');
        $this->EnableAction('Shelly_State2');
        $this->RegisterVariableFloat('Shelly_Power2','Power 3','');
        $this->RegisterVariableFloat('Shelly_Energy2','Energy 3','');

        $this->RegisterVariableBoolean('Shelly_State3','State 4','~Switch');
        $this->EnableAction('Shelly_State3');
        $this->RegisterVariableFloat('Shelly_Power3','Power 4','');
        $this->RegisterVariableFloat('Shelly_Energy3','Energy 4','');
    }

    public function ReceiveData($JSONString)
    {
        // Relay
        //shellies/shellyswitch-<deviceid>/relay/<i>
        //shellies/shellyswitch-<deviceid>/relay/power
        //shellies/shellyswitch-<deviceid>/relay/energy

        //Roller
        //shellies/shellyswitch-<deviceid>/roller/0

        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $Buffer = json_decode($data->Buffer);
            $this->SendDebug('MQTT Topic', $Buffer->TOPIC, 0);

            //Power Variable prüfen
            if (property_exists($Buffer, 'TOPIC')) {
                //Ist es ein Relay?
                if (fnmatch('*/relay/[0123]', $Buffer->TOPIC)) {
                    $this->SendDebug('State Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('State Msg', $Buffer->MSG, 0);
                    $ShellyTopic = explode("/", $Buffer->TOPIC);
                    $LastKey = count($ShellyTopic) - 1;
                    $relay = $ShellyTopic[$LastKey];
                    $this->SendDebug(__FUNCTION__.' Relay',$relay,0);

                    //Power prüfen und in IPS setzen
                    switch ($Buffer->MSG) {
                        case 'off':
                            switch ($relay) {
                                case 0:
                                    SetValue($this->GetIDForIdent('Shelly_State'), 0);
                                    break;
                                case 1:
                                    SetValue($this->GetIDForIdent('Shelly_State1'), 0);
                                    break;
                                case 2:
                                    SetValue($this->GetIDForIdent('Shelly_State2'), 0);
                                    break;
                                case 3:
                                    SetValue($this->GetIDForIdent('Shelly_State3'), 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'on':
                            switch ($relay) {
                                case 0:
                                    SetValue($this->GetIDForIdent('Shelly_State'), 1);
                                    break;
                                case 1:
                                    SetValue($this->GetIDForIdent('Shelly_State1'), 1);
                                    break;
                                case 2:
                                    SetValue($this->GetIDForIdent('Shelly_State2'), 1);
                                    break;
                                case 3:
                                    SetValue($this->GetIDForIdent('Shelly_State3'), 1);
                                    break;
                                default:
                                    break;
                            }
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
                if (fnmatch('*/relay/[0123]/power*', $Buffer->TOPIC)) {
                    $this->SendDebug('Power Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Power Msg', $Buffer->MSG, 0);
                    $ShellyTopic = explode("/", $Buffer->TOPIC);
                    $Key = count($ShellyTopic) - 2;
                    $relay = $ShellyTopic[$Key];

                    switch ($relay) {
                        case 0:
                            SetValue($this->GetIDForIdent('Shelly_Power'), $Buffer->MSG);
                            break;
                        case 1:
                            SetValue($this->GetIDForIdent('Shelly_Power1'), $Buffer->MSG);
                            break;
                        case 2:
                            SetValue($this->GetIDForIdent('Shelly_Power2'), $Buffer->MSG);
                            break;
                        case 3:
                            SetValue($this->GetIDForIdent('Shelly_Power3'), $Buffer->MSG);
                            break;
                        default:
                            $this->SendDebug('Relay Power', 'Undefined Relay: '.$relay, 0);
                            break;
                    }
                }
                if (fnmatch('*/relay/[0123]/energy*', $Buffer->TOPIC)) {
                    $this->SendDebug('Energy Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Energy Msg', $Buffer->MSG, 0);
                    $ShellyTopic = explode("/", $Buffer->TOPIC);
                    $Key = count($ShellyTopic) - 2;
                    $relay = $ShellyTopic[$Key];

                    switch ($relay) {
                        case 0:
                            SetValue($this->GetIDForIdent('Shelly_Energy'), $Buffer->MSG);
                            break;
                        case 1:
                            SetValue($this->GetIDForIdent('Shelly_Energy1'), $Buffer->MSG);
                            break;
                        case 2:
                            SetValue($this->GetIDForIdent('Shelly_Energy2'), $Buffer->MSG);
                            break;
                        case 3:
                            SetValue($this->GetIDForIdent('Shelly_Energy3'), $Buffer->MSG);
                            break;
                        default:
                            $this->SendDebug('Relay Energy', 'Undefined Relay: '.$relay, 0);
                            break;
                    }
                }
            }
        }
    }
}
