<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_Shelly1 extends IPSModule
{
    use Shelly,
        ShellyRelayAction;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('Device', '');
        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');

        $this->EnableAction('Shelly_State');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        if (($this->ReadPropertyString('Device') == 'shelly1pm')) {
            $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '');
            $this->RegisterVariableBoolean('Shelly_Overtemperature', $this->Translate('Overtemperature'), '');
            $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
        }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $Buffer = $data;
            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);

            //Power Variable prüfen
            if (property_exists($Buffer, 'Topic')) {
                //Ist es ein Shell1y1? Wenn ja weiter machen!
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    //Power prüfen und in IPS setzen
                    switch ($Buffer->Payload) {
                        case 'off':
                            SetValue($this->GetIDForIdent('Shelly_State'), 0);
                            break;
                        case 'on':
                            SetValue($this->GetIDForIdent('Shelly_State'), 1);
                            break;
                    }
                }
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Temperature'), $Buffer->Payload);
                }
                if (fnmatch('*/overtemperature', $Buffer->Topic)) {
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Overtemperature'), boolval($Buffer->Payload));
                }
                if (fnmatch('*/relay/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power'), $Buffer->Payload);
                }
            }
        }
    }

    private function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 0);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 0) {
                throw new Exception($this->Translate('Variable profile type does not match for profile') . $Name);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }

    private function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }

        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);

        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }
}
