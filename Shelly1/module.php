<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Shelly1 extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Input', 'Input', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Longpush', 'Longpush', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', ['shelly1pm', 'shelly1l'], '', false, true],
        ['Shelly_Overtemperature', 'Overtemperature', VARIABLETYPE_FLOAT, '', ['shelly1pm', 'shelly1l'], '', false, true],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', ['shelly1pm', 'shelly1l'], '', false, true],
        ['Shelly_Energy', 'Energy', VARIABLETYPE_FLOAT, '~Electricity', ['shelly1pm', 'shelly1l'], '', false, true],
        ['Shelly_ExtSwitch0', 'External Switch 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_ExtSwitch1', 'External Switch 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_ExtSwitch2', 'External Switch 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_ExtTemperature0', 'External Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_ExtTemperature1', 'External Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_ExtTemperature2', 'External Temperature 3', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_ExtHumidity0', 'External Humidity', VARIABLETYPE_FLOAT, '~Humidity.F', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', [], '', false, true]
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
                if (fnmatch('*/longpush/0', $Buffer->Topic)) {
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
                if (fnmatch('*/ext_switch/[012]', $Buffer->Topic)) {
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($input) {
                        case 0:
                            $this->SetValue('Shelly_ExtSwitch0', $Buffer->Payload);
                            break;
                        case 1:
                            $this->SetValue('Shelly_ExtSwitch1', $Buffer->Payload);
                            break;
                        case 2:
                            $this->SetValue('Shelly_ExtSwitch2', $Buffer->Payload);
                            break;
                    }
                }
                if (fnmatch('*/ext_temperature/[012]', $Buffer->Topic)) {
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($input) {
                        case 0:
                            $this->SetValue('Shelly_ExtTemperature0', $Buffer->Payload);
                            break;
                        case 1:
                            $this->SetValue('Shelly_ExtTemperature1', $Buffer->Payload);
                            break;
                        case 2:
                            $this->SetValue('Shelly_ExtTemperature2', $Buffer->Payload);
                            break;
                    }
                }
                if (fnmatch('*/ext_humidity/0', $Buffer->Topic)) {
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
