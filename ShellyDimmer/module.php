<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyDimmer extends IPSModule
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

        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');
        $this->RegisterVariableInteger('Shelly_Brightness', $this->Translate('Brightness'), 'Intensity.100');
        $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
        $this->RegisterVariableBoolean('Shelly_Overtemperature', $this->Translate('Overtemperature'), '');
        $this->RegisterVariableBoolean('Shelly_Overload', $this->Translate('Overload'), '');
        $this->RegisterVariableBoolean('Shelly_Loaderror', $this->Translate('Loaderror'), '');

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);

        $this->RegisterVariableBoolean('Shelly_Reachable', $this->Translate('Reachable'), 'Shelly.Reachable');

        $this->EnableAction('Shelly_State');
        $this->EnableAction('Shelly_Brightness');
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
            case 'Shelly_Brightness':
                $this->DimSet(intval($Value));
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
                if (fnmatch('*/light/0', $Buffer->Topic)) {
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
                if (fnmatch('*status*', $Buffer->Topic)) {
                    $Payload = json_decode($Buffer->Payload);
                    $this->SetValue('Shelly_State', $Payload->ison);
                    if ($Payload->ison) {
                        $this->SetValue('Shelly_Brightness', $Payload->brightness);
                    } else {
                        $this->SetValue('Shelly_Brightness', 0);
                    }
                }
                if (fnmatch('*/light/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power', $Buffer->Payload);
                }
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SendDebug('Temperature Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/overtemperature', $Buffer->Topic)) {
                    $this->SendDebug('Overtemperature Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Overtemperature', boolval($Buffer->Payload));
                }
                if (fnmatch('*/overload', $Buffer->Topic)) {
                    $this->SendDebug('Overload Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Overload', $Buffer->Payload);
                }
                if (fnmatch('*/loaderror', $Buffer->Topic)) {
                    $this->SendDebug('Loaderror Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Loaderror', $Buffer->Payload);
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
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/light/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }

    private function DimSet(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/light/0/set';
        $Payload['turn'] = 'off';
        if ($value > 0) {
            $Payload['brightness'] = strval($value);
            $Payload['turn'] = 'on';
        }

        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }
}
