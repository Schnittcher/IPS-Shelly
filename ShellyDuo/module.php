<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php';

class ShellyDuo extends ShellyModule
{
    use ColorHelper;

    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true],
        ['Shelly_White', 'White', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true],
        ['Shelly_ColorTemperature', 'Color Temperature', VARIABLETYPE_INTEGER, 'ShellyDuo.ColorTemperature', [], '', true, true],
        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProfileInteger('ShellyDuo.ColorTemperature', 'Intensity', '', 'K', 2700, 6500, 1);
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
            case 'Shelly_ColorTemperature':
                $this->ColorTemperatureSet(intval($Value));
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
                if (fnmatch('*status*', $Buffer->Topic)) {
                    $Payload = json_decode($Buffer->Payload);
                    $this->SetValue('Shelly_State', $Payload->ison);
                    $this->SetValue('Shelly_Brightness', $Payload->brightness);
                    $this->SetValue('Shelly_ColorTemperature', $Payload->temp);
                }
                if (fnmatch('*/light/0/power', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Power', $Buffer->Payload);
                }

                if (fnmatch('*/energy', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Energy', $Buffer->Payload);
                }
                if (fnmatch('*/online', $Buffer->Topic)) {
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

    public function setExtOpt($Payload)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/' . $this->ReadPropertyString('Device') . '/0/set';
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    public function DimSet(int $value, int $transition = 0)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/' . $this->ReadPropertyString('Device') . '/0/set';
        $Payload['brightness'] = strval($value);
        $Payload['transition'] = strval($transition);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    private function SwitchMode(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/' . $this->ReadPropertyString('Device') . '/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }

    private function ColorTemperatureSet(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/' . $this->ReadPropertyString('Device') . '/0/set';
        $Payload['temp'] = strval($value);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }
}