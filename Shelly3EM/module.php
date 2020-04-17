<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class Shelly3EM extends IPSModule
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

        $this->RegisterVariableFloat('Shelly_Power0', $this->Translate('Power') . ' A', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_PowerFactor0', $this->Translate('Power Factor') . ' A', '');
        $this->RegisterVariableFloat('Shelly_Current0', $this->Translate('Current') . ' A', '~Ampere');
        $this->RegisterVariableFloat('Shelly_Voltage0', $this->Translate('Voltage') . ' A', '~Volt');

        $this->RegisterVariableFloat('Shelly_Power1', $this->Translate('Power') . ' B', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_PowerFactor1', $this->Translate('Power Factor') . ' B', '');
        $this->RegisterVariableFloat('Shelly_Current1', $this->Translate('Current') . ' B', '~Ampere');
        $this->RegisterVariableFloat('Shelly_Voltage1', $this->Translate('Voltage') . ' B', '~Volt');

        $this->RegisterVariableFloat('Shelly_Power2', $this->Translate('Power') . ' C', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_PowerFactor2', $this->Translate('Power Factor') . ' C', '');
        $this->RegisterVariableFloat('Shelly_Current2', $this->Translate('Current') . ' C', '~Ampere');
        $this->RegisterVariableFloat('Shelly_Voltage2', $this->Translate('Voltage') . ' C', '~Volt');

        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State') . '1', '~Switch');
        $this->EnableAction('Shelly_State');

        $this->RegisterVariableBoolean('Shelly_State2', $this->Translate('State') . '2', '~Switch');
        $this->EnableAction('Shelly_State2');

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

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Relay 0 Payload', $Buffer->Payload, 0);
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

                if (fnmatch('*/relay/1', $Buffer->Topic)) {
                    $this->SendDebug('Relay 1 Payload', $Buffer->Payload, 0);
                    //Power prüfen und in IPS setzen
                    switch ($Buffer->Payload) {
                        case 'off':
                            SetValue($this->GetIDForIdent('Shelly_State2'), 0);
                            break;
                        case 'on':
                            SetValue($this->GetIDForIdent('Shelly_State2'), 1);
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

                //Phase A
                if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power0'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/pf', $Buffer->Topic)) {
                    $this->SendDebug('Power Factor Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_PowerFactor0'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/current', $Buffer->Topic)) {
                    $this->SendDebug('Current Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Current0'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Voltage0'), floatval($Buffer->Payload));
                }

                //Phase B
                if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power1'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/pf', $Buffer->Topic)) {
                    $this->SendDebug('Power Factor Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_PowerFactor1'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/current', $Buffer->Topic)) {
                    $this->SendDebug('Current Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Current1'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Voltage1'), floatval($Buffer->Payload));
                }

                //Phase C
                if (fnmatch('*emeter/2/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power2'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/pf', $Buffer->Topic)) {
                    $this->SendDebug('Power Factor Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_PowerFactor2'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/current', $Buffer->Topic)) {
                    $this->SendDebug('Current Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Current2'), floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    SetValue($this->GetIDForIdent('Shelly_Voltage2'), floatval($Buffer->Payload));
                }
            }
        }
    }
}
