<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php';

class ShellyRGBW2 extends ShellyModule
{
    use ColorHelper;

    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], 'White', true, true],
        ['Shelly_State2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', [], 'White', true, true],
        ['Shelly_State3', 'State 4', VARIABLETYPE_BOOLEAN, '~Switch', [], 'White', true, true],
        ['Shelly_Color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', [], 'Color', true, true],
        ['Shelly_White', 'White', VARIABLETYPE_INTEGER, '~Intensity.100', [], 'Color', true, true],
        ['Shelly_Gain', 'Gain', VARIABLETYPE_INTEGER, '~Intensity.100', [], 'Color', true, true],
        ['Shelly_Effect', 'Effect', VARIABLETYPE_INTEGER, 'Shelly.Effect', [], 'Color', true, true],
        ['Shelly_Brightness', 'Brightness 1', VARIABLETYPE_INTEGER, '~Intensity.100', [], 'White', true, true],
        ['Shelly_Brightness1', 'Brightness 2', VARIABLETYPE_INTEGER, '~Intensity.100', [], 'White', true, true],
        ['Shelly_Brightness2', 'Brightness 3', VARIABLETYPE_INTEGER, '~Intensity.100', [], 'White', true, true],
        ['Shelly_Brightness3', 'Brightness 4', VARIABLETYPE_INTEGER, '~Intensity.100', [], 'White', true, true],
        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Shelly_Power1', 'Power 2', VARIABLETYPE_FLOAT, '', [], 'White', false, true],
        ['Shelly_Power2', 'Power 3', VARIABLETYPE_FLOAT, '', [], 'White', false, true],
        ['Shelly_Power3', 'Power 4', VARIABLETYPE_FLOAT, '', [], 'White', false, true],
        ['Shelly_Overpower', 'Overpower', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Shelly_Overpower1', 'Overpower 2', VARIABLETYPE_BOOLEAN, '~Alert', [], 'White', false, true],
        ['Shelly_Overpower2', 'Overpower 3', VARIABLETYPE_BOOLEAN, '~Alert', [], 'White', false, true],
        ['Shelly_Overpower3', 'Overpower 4',  VARIABLETYPE_BOOLEAN, '~Alert', [], 'White', false, true],
        ['Shelly_Input', 'Input', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Longpush', 'Longpush', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyString('DeviceType', '-');
        $this->RegisterProfileIntegerEx('Shelly.Effect', 'Bulb', '', '', [
            [0, $this->Translate('Off'), 'Bulb', -1],
            [1, $this->Translate('Meteor Shower'), 'Bulb', -1],
            [2, $this->Translate('Gradual Change'), 'Bulb', -1],
            [3, $this->Translate('Breath'), 'Bulb', -1],
            [4, $this->Translate('Flash'), 'Bulb', -1],
            [5, $this->Translate('On/Off Gradual'), 'Bulb', -1],
            [6, $this->Translate('Red/Green Change'), 'Bulb', -1]
        ]);
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
        $this->SendDebug('ShlleyRGBW2 DeviceType', $this->ReadPropertyString('DeviceType'), 0);
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
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
                                if (strtolower($this->ReadPropertyString('DeviceType')) != $Payload->mode) {
                                    $this->SendDebug('DeviceType', strtolower($this->ReadPropertyString('DeviceType')) . ' ' . $Payload->mode, 0);
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
                                if (strtolower($this->ReadPropertyString('DeviceType')) != $Payload->mode) {
                                    $this->SendDebug('DeviceType', strtolower($this->ReadPropertyString('DeviceType')) . ' ' . $Payload->mode, 0);
                                    break;
                                }
                                $this->SetValue('Shelly_State', $Payload->ison);
                                $this->SetValue('Shelly_Color', $this->RGBToHex($Payload->red, $Payload->green, $Payload->blue));
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
        $Mode = strtolower($this->ReadPropertyString('DeviceType'));
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
