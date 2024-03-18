<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php';

class ShellyBulb extends ShellyModule
{
    use ColorHelper;

    public static $Variables = [
        ['Shelly_Mode', 'Mode', VARIABLETYPE_STRING, 'ShellyBulb.Mode', [], '', true, true, false],
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Shelly_Effect', 'Effect', VARIABLETYPE_INTEGER, 'ShellyBulb.Effect', [], '', true, true, false],

        ['Shelly_Color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', [], '', true, true, false],
        ['Shelly_Gain', 'Gain', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true, false],
        ['Shelly_White', 'White', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true, false],

        ['Shelly_Brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true, false],
        ['Shelly_ColorTemperature', 'Color Temperature', VARIABLETYPE_INTEGER, 'ShellyBulb.ColorTemperature', [], '', true, true, false],

        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterProfileStringEx('ShellyBulb.Mode', 'Menu', '', '', [
            ['white', $this->Translate('White'), '', 0xFFFFFF],
            ['color', $this->Translate('Color'), '', 0x0000FF]
        ]);
        $this->RegisterProfileInteger('ShellyBulb.ColorTemperature', 'Intensity', '', 'K', 3000, 6500, 1);

        $this->RegisterProfileIntegerEx('ShellyBulb.Effect', 'Menu', '', '', [
            [0, $this->Translate('Off'), '', 0x000000],
            [1, $this->Translate('Meteor Shower'), '', 0x000000],
            [2, $this->Translate('Gradual Change'), '', 0x000000],
            [3, $this->Translate('Flash'), '', 0x000000]
        ]);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode($Value);
                break;
            case 'Shelly_Effect':
                $this->SetEffect($Value);
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
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/light/0', $Buffer->Topic)) {
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
                    if (property_exists($Payload, 'effect')) {
                        $this->SetValue('Shelly_Effect', $Payload->effect);
                    }
                }
                if (fnmatch('*/light/0/power', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Power', $Buffer->Payload);
                }

                if (fnmatch('*/energy', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Energy', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            $this->SetValue('Shelly_Reachable', true);
                            break;
                        case 'false':
                            $this->SetValue('Shelly_Reachable', false);
                            $this->zeroingValues();
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

    public function SetEffect(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['mode'] = 'color';
        $Payload['effect'] = $value;
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
                IPS_SetHidden($this->GetIDForIdent('Shelly_White'), true);
                break;
            case 'white':
                IPS_SetHidden($this->GetIDForIdent('Shelly_Brightness'), false);
                IPS_SetHidden($this->GetIDForIdent('Shelly_ColorTemperature'), false);

                IPS_SetHidden($this->GetIDForIdent('Shelly_Color'), true);
                IPS_SetHidden($this->GetIDForIdent('Shelly_Gain'), true);
                IPS_SetHidden($this->GetIDForIdent('Shelly_White'), false);
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
        switch ($value) {
            case 'color':
                $this->SetColor($this->GetValue('Shelly_Color'));
                break;
            case 'white':
                $this->DimSet($this->GetValue('Shelly_ColorTemperature'));
                break;
        }
    }

    private function WhiteSet(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['mode'] = 'color';
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