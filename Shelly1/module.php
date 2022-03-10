<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class Shelly1 extends IPSModule
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
        $this->SetReceiveDataFilter('.*' . MQTT_GROUP_TOPIC . '/' . $MQTTTopic . '.*');

        if (($this->ReadPropertyString('Device') == 'shelly1pm') || ($this->ReadPropertyString('Device') == 'shelly1l')) {
            $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '~Watt.3680');
            $this->RegisterVariableBoolean('Shelly_Overtemperature', $this->Translate('Overtemperature'), '');
            $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
            $this->RegisterVariableFloat('Shelly_Energy', $this->Translate('Energy'), '~Electricity');
        }
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

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'off':
                            $this->SetValue('Shelly_State', 0);
                            break;
                        case 'on':
                            $this->SetValue('Shelly_State', 1);
                            break;
                    }
                }
                if (fnmatch('*/input/0', $Buffer->Topic)) {
                    $this->SendDebug('Input Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 0:
                            $this->SetValue('Shelly_Input', 0);
                            break;
                        case 1:
                            $this->SetValue('Shelly_Input', 1);
                            break;
                    }
                }
                if (fnmatch('*/longpush/0', $Buffer->Topic)) {
                    $this->SendDebug('Longpush Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 0:
                            $this->SetValue('Shelly_Longpush', 0);
                            break;
                        case 1:
                            $this->SetValue('Shelly_Longpush', 1);
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
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/overtemperature', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Overtemperature', boolval($Buffer->Payload));
                }
                if (fnmatch('*/relay/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power', $Buffer->Payload);
                }
                if (fnmatch('*/relay/0/energy*', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Energy', $Buffer->Payload / 60000);
                }
                if (fnmatch('*/ext_switch/[012]', $Buffer->Topic)) {
                    $this->SendDebug('ext_switch Payload', $Buffer->Payload, 0);
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($input) {
                        case 0:
                            $this->RegisterVariableBoolean('Shelly_ExtSwitch0', $this->Translate('External Switch 1'), '~Switch');
                            $this->SetValue('Shelly_ExtSwitch0', $Buffer->Payload);
                            break;
                        case 1:
                            $this->RegisterVariableBoolean('Shelly_ExtSwitch1', $this->Translate('External Switch 2'), '~Switch');
                            $this->SetValue('Shelly_ExtSwitch1', $Buffer->Payload);
                            break;
                        case 2:
                            $this->RegisterVariableBoolean('Shelly_ExtSwitch2', $this->Translate('External Switch 3'), '~Switch');
                            $this->SetValue('Shelly_ExtSwitch2', $Buffer->Payload);
                            break;
                    }
                }
                if (fnmatch('*/ext_temperature/[012]', $Buffer->Topic)) {
                    $this->SendDebug('ext_temperature Payload', $Buffer->Payload, 0);
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($input) {
                        case 0:
                            $this->RegisterVariableFloat('Shelly_ExtTemperature0', $this->Translate('External Temperature 1'), '~Temperature');
                            $this->SetValue('Shelly_ExtTemperature0', $Buffer->Payload);
                            break;
                        case 1:
                            $this->RegisterVariableFloat('Shelly_ExtTemperature1', $this->Translate('External Temperature 2'), '~Temperature');
                            $this->SetValue('Shelly_ExtTemperature1', $Buffer->Payload);
                            break;
                        case 2:
                            $this->RegisterVariableFloat('Shelly_ExtTemperature2', $this->Translate('External Temperature 3'), '~Temperature');
                            $this->SetValue('Shelly_ExtTemperature2', $Buffer->Payload);
                            break;
                    }
                }
                if (fnmatch('*/ext_humidity/0', $Buffer->Topic)) {
                    $this->RegisterVariableInteger('Shelly_ExtHumidity0', $this->Translate('External Humidity'), '~Humidity');
                    $this->SetValue('Shelly_ExtHumidity0', $Buffer->Payload);
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
