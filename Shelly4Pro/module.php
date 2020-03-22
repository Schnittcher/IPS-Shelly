<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class Shelly4Pro extends IPSModule
{
    use Shelly;
    use
        ShellyRelayAction;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        $this->SendDebug(__FUNCTION__ . ' Device Type: ', ' Relay', 0);
        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');
        $this->EnableAction('Shelly_State');
        $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Energy', $this->Translate('Energy'), '~Electricity');

        $this->RegisterVariableBoolean('Shelly_State1', $this->Translate('State') . ' 2', '~Switch');
        $this->EnableAction('Shelly_State1');
        $this->RegisterVariableFloat('Shelly_Power1', $this->Translate('Power') . ' 2', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Energy1', $this->Translate('Energy') . ' 2', '~Electricity');

        $this->RegisterVariableBoolean('Shelly_State2', $this->Translate('State') . ' 3', '~Switch');
        $this->EnableAction('Shelly_State2');
        $this->RegisterVariableFloat('Shelly_Power2', $this->Translate('Power') . ' 3', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Energy2', $this->Translate('Energy') . ' 3', '~Electricity');

        $this->RegisterVariableBoolean('Shelly_State3', $this->Translate('State') . ' 4', '~Switch');
        $this->EnableAction('Shelly_State3');
        $this->RegisterVariableFloat('Shelly_Power3', $this->Translate('Power') . ' 4', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Energy3', $this->Translate('Energy ') . ' 4', '~Electricity');
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
                    $this->SendDebug('State Topic', $Buffer->Topic, 0);
                    $this->SendDebug('State Payload', $Buffer->Payload, 0);
                    $relay = $this->getChannelRelay($Buffer->Topic);
                    $this->SendDebug(__FUNCTION__ . ' Relay', $relay, 0);

                    //Power prüfen und in IPS setzen
                    switch ($Buffer->Payload) {
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
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $ShellyTopic = explode('/', $Buffer->Topic);
                    $Key = count($ShellyTopic) - 2;
                    $relay = $ShellyTopic[$Key];

                    switch ($relay) {
                        case 0:
                            SetValue($this->GetIDForIdent('Shelly_Power'), $Buffer->Payload);
                            break;
                        case 1:
                            SetValue($this->GetIDForIdent('Shelly_Power1'), $Buffer->Payload);
                            break;
                        case 2:
                            SetValue($this->GetIDForIdent('Shelly_Power2'), $Buffer->Payload);
                            break;
                        case 3:
                            SetValue($this->GetIDForIdent('Shelly_Power3'), $Buffer->Payload);
                            break;
                        default:
                            $this->SendDebug('Relay Power', 'Undefined Relay: ' . $relay, 0);
                            break;
                    }
                }
                if (fnmatch('*/relay/[0123]/energy*', $Buffer->Topic)) {
                    $this->SendDebug('Energy Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $ShellyTopic = explode('/', $Buffer->Topic);
                    $Key = count($ShellyTopic) - 2;
                    $relay = $ShellyTopic[$Key];

                    switch ($relay) {
                        case 0:
                            SetValue($this->GetIDForIdent('Shelly_Energy'), $Buffer->Payload / 60000);
                            break;
                        case 1:
                            SetValue($this->GetIDForIdent('Shelly_Energy1'), $Buffer->Payload / 60000);
                            break;
                        case 2:
                            SetValue($this->GetIDForIdent('Shelly_Energy2'), $Buffer->Payload / 60000);
                            break;
                        case 3:
                            SetValue($this->GetIDForIdent('Shelly_Energy3'), $Buffer->Payload / 60000);
                            break;
                        default:
                            $this->SendDebug('Relay Energy', 'Undefined Relay: ' . $relay, 0);
                            break;
                    }
                }
            }
        }
    }
}
