<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_ShellyPlug extends IPSModule
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

        $this->SendDebug(__FUNCTION__ . ' Device Type: ', ' Relay', 0);
        $this->RegisterVariableBoolean('Shelly_State', 'State', '~Switch');
        $this->EnableAction('Shelly_State');
        $this->RegisterVariableFloat('Shelly_Power', 'Power', '');
        $this->RegisterVariableFloat('Shelly_Energy', 'Energy', '');
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $Buffer = json_decode($data->Buffer);
            $this->SendDebug('MQTT Topic', $Buffer->TOPIC, 0);

            //Power Variable prüfen
            if (property_exists($Buffer, 'TOPIC')) {
                //Ist es ein Relay?
                if (fnmatch('*/relay/0', $Buffer->TOPIC)) {
                    $this->SendDebug('State Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('State Msg', $Buffer->MSG, 0);
                    //Power prüfen und in IPS setzen
                    switch ($Buffer->MSG) {
                        case 'off':
                            SetValue($this->GetIDForIdent('Shelly_State'), 0);
                            break;
                        case 'on':
                            SetValue($this->GetIDForIdent('Shelly_State'), 1);
                            break;
                        case 'overpower':
                            //TODO
                            break;
                    }
                }
                if (fnmatch('*/relay/0/power*', $Buffer->TOPIC)) {
                    $this->SendDebug('Power Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Power Msg', $Buffer->MSG, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power'), $Buffer->MSG);
                }
                if (fnmatch('*/relay/0/energy*', $Buffer->TOPIC)) {
                    $this->SendDebug('Energy Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Energy Msg', $Buffer->MSG, 0);
                    SetValue($this->GetIDForIdent('Shelly_Energy'), $Buffer->MSG);
                }
            }
        }
    }
}
