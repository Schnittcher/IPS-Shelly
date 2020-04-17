<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyEM extends IPSModule
{
    use Shelly;
    use VariableProfileHelper;
    use MQTTHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

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

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode($Value);
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
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Relay Payload', $Buffer->Payload, 0);
                    //Power prüfen und in IPS setzen
                    switch ($Buffer->Payload) {
                        case 'off':
                            $this->SetValue('Shelly_State', 0);
                            break;
                        case 'on':
                            $this->SetValue('Shelly_State', 1);
                            break;
                    }
                }
                //Emter 0
                if (fnmatch('*emeter/0/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Energy0', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/0/returned_energy', $Buffer->Topic)) {
                    $this->SendDebug('Returned Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReturnedEnergy0', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power0', $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/reactive_power', $Buffer->Topic)) {
                    $this->SendDebug('Reactive Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReactivePower0', $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage0', $Buffer->Payload);
                }

                //Emter 1
                if (fnmatch('*emeter/1/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Energy1', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/1/returned_energy', $Buffer->Topic)) {
                    $this->SendDebug('Returned Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReturnedEnergy1', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power1', $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/reactive_power', $Buffer->Topic)) {
                    $this->SendDebug('Reactive Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReactivePower1', $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage1', $Buffer->Payload);
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

    private function SwitchMode(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}
