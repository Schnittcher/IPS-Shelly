<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyAir extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Overtemperature', 'Overtemperature', VARIABLETYPE_BOOLEAN, '', [], '', false, true],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_Totalworktime', 'Total Work Time', VARIABLETYPE_INTEGER, '~Temperature', [], '', false, true],
        ['Shelly_Input', 'Input', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],

        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

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
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
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
                    switch ($Buffer->Payload) {
                        case 0:
                            $this->SetValue('Shelly_Input', 0);
                            break;
                        case 1:
                            $this->SetValue('Shelly_Input', 1);
                            break;
                    }
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
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/overtemperature', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Overtemperature', boolval($Buffer->Payload));
                }
                if (fnmatch('*/relay/0/power', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Power', $Buffer->Payload);
                }
                if (fnmatch('*/relay/0/energy*', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Energy', $Buffer->Payload / 60000);
                }
                if (fnmatch('*/ext_temperature/[012]', $Buffer->Topic)) {
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
                if (fnmatch('*/totalworktime', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Totalworktime', $Buffer->Payload);
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