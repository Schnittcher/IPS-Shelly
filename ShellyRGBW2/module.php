<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyRGBW2 extends IPSModule
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
        $this->RegisterPropertyString('Mode', '-');

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

        switch ($this->ReadPropertyString('Mode')) {
            case '-':
                $this->SendDebug(__FUNCTION__, 'No Mode set', 0);
                break;
            case 'Color':
                $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');
                $this->EnableAction('Shelly_State');

                $this->RegisterVariableInteger('Shelly_Color', $this->Translate('Color'), '~HexColor');
                $this->EnableAction('Shelly_Color');

                $this->RegisterVariableInteger('Shelly_White', $this->Translate('White'), 'Intensity.255');
                $this->EnableAction('Shelly_White');

                $this->RegisterVariableInteger('Shelly_Gain', $this->Translate('Gain'), 'Intensity.100');
                $this->EnableAction('Shelly_Gain');

                $this->RegisterProfileIntegerEx('Shelly.Effect', 'Bulb', '', '', [
                    [0, $this->Translate('Off'), 'Bulb', -1],
                    [1, $this->Translate('Meteor Shower'), 'Bulb', -1],
                    [2, $this->Translate('Gradual Change'), 'Bulb', -1],
                    [3, $this->Translate('Breath'), 'Bulb', -1],
                    [4, $this->Translate('Flash'), 'Bulb', -1],
                    [5, $this->Translate('On/Off Gradual'), 'Bulb', -1],
                    [6, $this->Translate('Red/Green Change'), 'Bulb', -1]
                ]);
                $this->RegisterVariableInteger('Shelly_Effect', $this->Translate('Effect'), 'Shelly.Effect');
                $this->EnableAction('Shelly_Effect');

                $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower', $this->Translate('Overpower'), '');
                break;
            case 'White':
                $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State 1'), '~Switch');
                $this->RegisterVariableBoolean('Shelly_State1', $this->Translate('State 2'), '~Switch');
                $this->RegisterVariableBoolean('Shelly_State2', $this->Translate('State 3'), '~Switch');
                $this->RegisterVariableBoolean('Shelly_State3', $this->Translate('State 4'), '~Switch');

                $this->EnableAction('Shelly_State');
                $this->EnableAction('Shelly_State1');
                $this->EnableAction('Shelly_State2');
                $this->EnableAction('Shelly_State3');

                $this->RegisterVariableInteger('Shelly_Brightness', $this->Translate('Brightness 1'), 'Intensity.100');
                $this->RegisterVariableInteger('Shelly_Brightness1', $this->Translate('Brightness 2'), 'Intensity.100');
                $this->RegisterVariableInteger('Shelly_Brightness2', $this->Translate('Brightness 3'), 'Intensity.100');
                $this->RegisterVariableInteger('Shelly_Brightness3', $this->Translate('Brightness 4'), 'Intensity.100');

                $this->EnableAction('Shelly_Brightness');
                $this->EnableAction('Shelly_Brightness1');
                $this->EnableAction('Shelly_Brightness2');
                $this->EnableAction('Shelly_Brightness3');

                $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power 1'), '');
                $this->RegisterVariableFloat('Shelly_Power1', $this->Translate('Power 2'), '');
                $this->RegisterVariableFloat('Shelly_Power2', $this->Translate('Power 3'), '');
                $this->RegisterVariableFloat('Shelly_Power3', $this->Translate('Power 4'), '');

                $this->RegisterVariableBoolean('Shelly_Overpower', $this->Translate('Overpower 1'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower1', $this->Translate('Overpower 2'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower2', $this->Translate('Overpower 3'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower3', $this->Translate('Overpower 4'), '');
                break;
        }
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode(0, $Value);
                break;
            case 'Shelly_State1':
                $this->SwitchMode(1, $Value);
                break;
            case 'Shelly_State2':
                $this->SwitchMode(2, $Value);
                break;
            case 'Shelly_State3':
                $this->SwitchMode(3, $Value);
                break;
            case 'Shelly_Brightness':
                $this->SetDimmer(0, $Value);
                break;
            case 'Shelly_Brightness1':
                $this->SetDimmer(1, $Value);
                break;
            case 'Shelly_Brightness2':
                $this->SetDimmer(2, $Value);
                break;
            case 'Shelly_Brightness3':
                $this->SetDimmer(3, $Value);
                break;
            case 'Shelly_Color':
                $this->SetColor($Value);
                break;
            case 'Shelly_White':
                $this->SetWhite($Value);
                break;
                case 'Shelly_Gain':
                    $this->SetGain($Value);
                    break;
            case 'Shelly_Effect':
                $this->SetEffect($Value);
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('ShlleyRGBW2 Mode', $this->ReadPropertyString('Mode'), 0);
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
                $channel = $this->getChannel($Buffer->Topic);
                $this->SendDebug('ShellyRGBW2 Payload', $Buffer->Payload, 0);
                $this->SendDebug('ShellyRGBW2 Channel', $channel, 0);
                $Payload = json_decode($Buffer->Payload);
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
                if (fnmatch('*status*', $Buffer->Topic)) {
                    switch ($Payload->mode) {
                            case 'white':
                                if (strtolower($this->ReadPropertyString('Mode')) != $Payload->mode) {
                                    $this->SendDebug('Mode', strtolower($this->ReadPropertyString('Mode')) . ' ' . $Payload->mode, 0);
                                    break;
                                }
                                switch ($channel) {
                                    case 0:
                                        $this->SetValue('Shelly_State', $Payload->ison);
                                        $this->SetValue('Shelly_Brightness', $Payload->brightness);
                                        $this->SetValue('Shelly_Power', $Payload->power);
                                        $this->SetValue('Shelly_Overpower', $Payload->overpower);
                                        break;
                                    case 1:
                                        $this->SetValue('Shelly_State1', $Payload->ison);
                                        $this->SetValue('Shelly_Brightness1', $Payload->brightness);
                                        $this->SetValue('Shelly_Power1', $Payload->power);
                                        $this->SetValue('Shelly_Overpower1', $Payload->overpower);
                                        break;
                                    case 2:
                                        $this->SetValue('Shelly_State2', $Payload->ison);
                                        $this->SetValue('Shelly_Brightness2', $Payload->brightness);
                                        $this->SetValue('Shelly_Power2', $Payload->power);
                                        $this->SetValue('Shelly_Overpower2', $Payload->overpower);
                                        break;
                                    case 3:
                                        $this->SetValue('Shelly_State3', $Payload->ison);
                                        $this->SetValue('Shelly_Brightness3', $Payload->brightness);
                                        $this->SetValue('Shelly_Power3', $Payload->power);
                                        $this->SetValue('Shelly_Overpower3', $Payload->overpower);
                                        break;
                                    default:
                                        break;
                                }
                                break;
                            case 'color':
                                if (strtolower($this->ReadPropertyString('Mode')) != $Payload->mode) {
                                    $this->SendDebug('Mode', strtolower($this->ReadPropertyString('Mode')) . ' ' . $Payload->mode, 0);
                                    break;
                                }
                                $this->SetValue('Shelly_State', $Payload->ison);
                                $this->SetValue('Shelly_Color', $this->rgbToHex($Payload->red, $Payload->green, $Payload->blue));
                                $this->SetValue('Shelly_White', $Payload->white);
                                $this->SetValue('Shelly_Gain', $Payload->gain);
                                $this->SetValue('Shelly_Effect', $Payload->effect);
                                $this->SetValue('Shelly_Power', $Payload->power);
                                $this->SetValue('Shelly_Overpower', $Payload->overpower);
                                break;
                            default:
                                $this->SendDebug('Invalid Mode', $Payload->mode, 0);
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
            }
        }
    }

    private function SetDimmer(int $channel, int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/white/' . $channel . '/set';
        $Payload['brightness'] = strval($value);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    private function SwitchMode(int $relay, bool $Value)
    {
        $Mode = strtolower($this->ReadPropertyString('Mode'));
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/' . $Mode . '/' . $relay . '/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }

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
        $Payload['red'] = strval($RGB[0]);
        $Payload['green'] = strval($RGB[1]);
        $Payload['blue'] = strval($RGB[2]);

        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    private function SetGain(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['gain'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    private function SetWhite(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['white'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    private function SetEffect(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['effect'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }
}
