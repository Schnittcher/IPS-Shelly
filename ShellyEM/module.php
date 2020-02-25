<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class ShellyEM extends IPSModule
{
    use Shelly;
    use
        ShellyRelayAction;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        $this->RegisterAttributeInteger('GatewayMode', 0); // 0 = MQTTServer 1 = MQTTClient

        $this->RegisterPropertyString('MQTTTopic', '');

        $this->RegisterVariableFloat('Shelly_Energy0', $this->Translate('Energy') . ' 0', '~Electricity');
        $this->RegisterVariableFloat('Shelly_ReturnedEnergy0', $this->Translate('Returned Energy') . ' 0', '~Electricity');
        $this->RegisterVariableFloat('Shelly_Power0', $this->Translate('Power') . ' 0', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_ReactivePower0', $this->Translate('Reactive Power') . ' 0', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Voltage0', $this->Translate('Voltage') . ' 0', '~Volt');

        $this->RegisterVariableFloat('Shelly_Energy1', $this->Translate('Energy') . ' 1', '~Electricity');
        $this->RegisterVariableFloat('Shelly_ReturnedEnergy1', $this->Translate('Returned Energy') . ' 1', '~Electricity');
        $this->RegisterVariableFloat('Shelly_Power1', $this->Translate('Power') . ' 1', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_ReactivePower1', $this->Translate('Reactive Power') . ' 1', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Voltage1', $this->Translate('Voltage') . ' 1', '~Volt');

        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');
        $this->EnableAction('Shelly_State');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
            case FM_CONNECT:
                //$this->LogMessage('parentGUID '. print_r($Data),KL_DEBUG);
                $parentGUID = IPS_GetInstance($Data[0])['ModuleInfo']['ModuleID'];
                switch ($parentGUID) {
                    case '{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}':
                        $this->WriteAttributeInteger('GatewayMode', 0);
                        break;
                    case '{EE0D345A-CF31-428A-A613-33CE98E752DD}':
                        $this->WriteAttributeInteger('GatewayMode', 1);
                        break;
                }
                break;
            default:
                break;
        }
    }

    public function ReceiveData($JSONString)
    {
        $GatewayMode = $this->ReadAttributeInteger('GatewayMode');
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);

            $this->SendDebug('GatewayMode', $GatewayMode, 0);
            if ($GatewayMode == 0) {
                $Buffer = $data;
            } else {
                $Buffer = json_decode($data->Buffer);
            }

            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);

            //Power Variable prüfen
            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Relay Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Relay Payload', $Buffer->Payload, 0);
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
                //Emter 0
                if (fnmatch('*emeter/0/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Energy0'), floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/0/returned_energy', $Buffer->Topic)) {
                    $this->SendDebug('Returned Energy Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Returned Energy Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_ReturnedEnergy0'), floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power0'), $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/reactive_power', $Buffer->Topic)) {
                    $this->SendDebug('Reactive Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Reactive Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_ReactivePower0'), $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Voltage0'), $Buffer->Payload);
                }

                //Emter 1
                if (fnmatch('*emeter/1/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Energy1'), floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/1/returned_energy', $Buffer->Topic)) {
                    $this->SendDebug('Returned Energy Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Returned Energy Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_ReturnedEnergy1'), floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power1'), $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/reactive_power', $Buffer->Topic)) {
                    $this->SendDebug('Reactive Power Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Reactive Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_ReactivePower1'), $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Topic', $Buffer->Topic, 0);
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Voltage1'), $Buffer->Payload);
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
