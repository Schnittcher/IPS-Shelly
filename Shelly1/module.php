<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class Shelly1 extends IPSModule
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
        $this->RegisterPropertyString('Device', '');
        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');

        $this->EnableAction('Shelly_State');

        $this->RegisterVariableBoolean('Shelly_Input', $this->Translate('Input'), '~Switch');
        $this->RegisterVariableBoolean('Shelly_Longpush', $this->Translate('Longpush'), '~Switch');

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);

        $this->RegisterVariableBoolean('Shelly_Reachable', $this->Translate('Reachable'), 'Shelly.Reachable');
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
            $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '~Watt.3680');
            $this->RegisterVariableBoolean('Shelly_Overtemperature', $this->Translate('Overtemperature'), '');
            $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
            $this->RegisterVariableFloat('Shelly_Energy', $this->Translate('Energy'), '~Electricity');
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
                //Ist es ein Shell1y1? Wenn ja weiter machen!
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
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
                if (fnmatch('*/input/0', $Buffer->Topic)) {
                    $this->SendDebug('Input Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 0:
                            SetValue($this->GetIDForIdent('Shelly_Input'), 0);
                            break;
                        case 1:
                            SetValue($this->GetIDForIdent('Shelly_Input'), 1);
                            break;
                    }
                }
                if (fnmatch('*/longpush/0', $Buffer->Topic)) {
                    $this->SendDebug('Longpush Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 0:
                            SetValue($this->GetIDForIdent('Shelly_Longpush'), 0);
                            break;
                        case 1:
                            SetValue($this->GetIDForIdent('Shelly_Longpush'), 1);
                            break;
                    }
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
                    $this->SendDebug('Online Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'true':
                            SetValue($this->GetIDForIdent('Shelly_Reachable'), true);
                            break;
                        case 'false':
                            SetValue($this->GetIDForIdent('Shelly_Reachable'), false);
                            break;
                    }
                }
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Temperature'), $Buffer->Payload);
                }
                if (fnmatch('*/overtemperature', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Overtemperature'), boolval($Buffer->Payload));
                }
                if (fnmatch('*/relay/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power'), $Buffer->Payload);
                }
                if (fnmatch('*/relay/0/energy*', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Energy'), $Buffer->Payload / 60000);
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
