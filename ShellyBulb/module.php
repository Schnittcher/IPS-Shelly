<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyBulb extends IPSModule
{
    use Shelly;
    use VariableProfileHelper;
    use MQTTHelper;
    use ColorHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('Device', '');

        $this->RegisterProfileStringEx('ShellyBulb.Mode', 'Menu', '', '', [
            ['white', $this->Translate('White'), '', 0xFFFFFF],
            ['color', $this->Translate('Color'), '', 0x0000FF]
        ]);

        $this->RegisterVariableString('Shelly_Mode', $this->Translate('Mode'), 'ShellyBulb.Mode', 1);
        $this->EnableAction('Shelly_Mode');
        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch', 2);

        //Mode Color
        $this->RegisterVariableInteger('Shelly_Color', $this->Translate('Color'), '~HexColor', 3);
        $this->RegisterVariableInteger('Shelly_Gain', $this->Translate('Gain'), '~Intensity.100', 4);
        $this->RegisterVariableInteger('Shelly_White', $this->Translate('White'), '~Intensity.100', 4);

        //Mode White
        $this->RegisterVariableInteger('Shelly_Brightness', $this->Translate('Brightness'), '~Intensity.100', 6);
        $this->RegisterProfileInteger('ShellyBulb.ColorTemperature', 'Intensity', '', 'K', 2700, 6500, 1);
        $this->RegisterVariableInteger('Shelly_ColorTemperature', $this->Translate('Color Temperature'), 'ShellyBulb.ColorTemperature', 7);

        //Both modes
        $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '~Watt.3680', 8);
        $this->RegisterVariableFloat('Shelly_Energy', $this->Translate('Energy') . ' 0', '~Electricity', 9);

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
        $this->RegisterVariableBoolean('Shelly_Reachable', $this->Translate('Reachable'), 'Shelly.Reachable', 10);

        $this->EnableAction('Shelly_State');
        $this->EnableAction('Shelly_Brightness');
        $this->EnableAction('Shelly_White');
        $this->EnableAction('Shelly_ColorTemperature');

        $this->EnableAction('Shelly_Color');
        $this->EnableAction('Shelly_Gain');
        $this->EnableAction('Shelly_White');
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
            case 'Shelly_White':
                $this->WhiteSet(intval($Value));
                break;
            case 'Shelly_ColorTemperature':
                $this->ColorTemperatureSet(intval($Value));
                break;
            case 'Shelly_Color':
                $this->SetColor($Value);
                break;
            case 'Shelly_Gain':
                $this->SetGain($Value);
                break;
            case 'Shelly_Mode':
                $this->SetValue('Shelly_Mode', $Value);
                $this->SetBulbMode($Value);
                $this->hideVariables($Value);
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
                if (fnmatch('*/color/0', $Buffer->Topic)) {
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

                    if (property_exists($Payload, 'brightness')) {
                        $this->SetValue('Shelly_Brightness', $Payload->brightness);
                    }
                    if (property_exists($Payload, 'temp')) {
                        $this->SetValue('Shelly_ColorTemperature', $Payload->temp);
                    }

                    if (property_exists($Payload, 'white')) {
                        $this->SetValue('Shelly_White', $Payload->white);
                    }

                    if (property_exists($Payload, 'red')) { //wenn red existiert, existieren auch die anderen
                        $this->SetValue('Shelly_Gain', $Payload->gain);
                        $this->SetValue('Shelly_Color', $this->RGBToHex($Payload->red, $Payload->green, $Payload->blue));
                    }
                    if (property_exists($Payload, 'mode')) {
                        $this->SetValue('Shelly_Mode', $Payload->mode);
                    }
                }
                if (fnmatch('*/light/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power', $Buffer->Payload);
                }

                if (fnmatch('*/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Energy', floatval($Buffer->Payload) / 60000);
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

    public function DimSet(int $value, int $transition = 0)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['mode'] = 'white';
        $Payload['brightness'] = strval($value);
        $Payload['transition'] = strval($transition);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    public function setExtOpt($Payload)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    private function hideVariables($mode)
    {
        switch ($mode) {
            case 'color':
                IPS_SetHidden($this->GetIDForIdent('Shelly_Brightness'), true);
                IPS_SetHidden($this->GetIDForIdent('Shelly_ColorTemperature'), true);

                IPS_SetHidden($this->GetIDForIdent('Shelly_Color'), false);
                IPS_SetHidden($this->GetIDForIdent('Shelly_Gain'), false);
                IPS_SetHidden($this->GetIDForIdent('Shelly_White'), false);
                break;
            case 'white':
            IPS_SetHidden($this->GetIDForIdent('Shelly_Brightness'), false);
            IPS_SetHidden($this->GetIDForIdent('Shelly_ColorTemperature'), false);

            IPS_SetHidden($this->GetIDForIdent('Shelly_Color'), true);
            IPS_SetHidden($this->GetIDForIdent('Shelly_Gain'), true);
            IPS_SetHidden($this->GetIDForIdent('Shelly_White'), true);
                break;
        }
    }

    private function SwitchMode(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }

    private function SetBulbMode($value)
    {

        //$Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        //$Payload['mode'] = strval($value);
        switch ($value) {
            case 'color':
                $this->SetColor($this->GetValue('Shelly_Color'));
                break;
            case 'white':
                $this->DimSet($this->GetValue('Shelly_ColorTemperature'));
                break;
        }
        //$Payload['mode'] = strval($value);
        //$Payload = json_encode($Payload);
        //$this->sendMQTT($Topic, $Payload);
    }

    private function WhiteSet(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['mode'] = 'white';
        $Payload['white'] = strval($value);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    private function ColorTemperatureSet(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['mode'] = 'white';
        $Payload['temp'] = strval($value);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    private function SetColor($color)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';

        //If $Value Hex Color convert to Decimal
        if (preg_match('/^#[a-f0-9]{6}$/i', strval($color))) {
            $color = hexdec($color);
        }

        $RGB = $this->HexToRGB(intval($color));
        $Payload['mode'] = 'color';
        $Payload['red'] = strval($RGB[0]);
        $Payload['green'] = strval($RGB[1]);
        $Payload['blue'] = strval($RGB[2]);

        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    private function SetGain(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['mode'] = 'color';
        $Payload['gain'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }
}