<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyUni extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_ADC', 'ADC', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Input', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Longpush', 'Longpush', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode(0, $Value);
                break;
            case 'Shelly_State1':
                $this->SwitchMode(1, $Value);
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
                if (fnmatch('*/relay/[01]', $Buffer->Topic)) {
                    $relay = $this->getChannelRelay($Buffer->Topic);
                    $this->SendDebug(__FUNCTION__ . ' Relay', $relay, 0);

                    switch ($Buffer->Payload) {
                        case 'off':
                            switch ($relay) {
                                case 0:
                                    $this->SetValue('Shelly_State', 0);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_State1', 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'on':
                            switch ($relay) {
                                case 0:
                                    $this->SetValue('Shelly_State', 1);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_State1', 1);
                                    break;
                                default:
                                    break;
                            }
                            break;
                    }
                }
                if (fnmatch('*/input/[01]', $Buffer->Topic)) {
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($Buffer->Payload) {
                        case 0:
                            switch ($input) {
                                case 0:
                                    $this->SetValue('Shelly_Input', 0);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_Input1', 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 1:
                            switch ($input) {
                                case 0:
                                    $this->SetValue('Shelly_Input', 1);
                                    break;
                                case 1:
                                    $this->SetValue('Shelly_Input1', 1);
                                    break;
                                default:
                                    break;
                            }
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
                if (fnmatch('*/adc/[01]', $Buffer->Topic)) {
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($input) {
                        case 0:
                            $this->SetValue('Shelly_ADC', $Buffer->Payload);
                            break;
                        case 1:
                            $this->SendDebug('ADC 2', $Buffer->Payload, 0);
                            //$this->SetValue('Shelly_ADC1', $Buffer->Payload);
                            break;
                        default:
                            break;
                    }
                }
                if (fnmatch('*/ext_temperature/[01234]', $Buffer->Topic)) {
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
                        case 3:
                            $this->RegisterVariableFloat('Shelly_ExtTemperature3', $this->Translate('External Temperature 4'), '~Temperature');
                            $this->SetValue('Shelly_ExtTemperature3', $Buffer->Payload);
                            break;
                        case 4:
                            $this->RegisterVariableFloat('Shelly_ExtTemperature4', $this->Translate('External Temperature 5'), '~Temperature');
                            $this->SetValue('Shelly_ExtTemperature4', $Buffer->Payload);
                            break;
                    }
                }
                if (fnmatch('*/ext_humidity/[01234]', $Buffer->Topic)) {
                    $input = $this->getChannelRelay($Buffer->Topic);
                    switch ($input) {
                        case 0:
                            $this->RegisterVariableFloat('Shelly_ExtHumidity0', $this->Translate('External Humidity 1'), '~Humidity.F');
                            $this->SetValue('Shelly_ExtHumidity0', $Buffer->Payload);
                            break;
                        case 1:
                            $this->RegisterVariableFloat('Shelly_ExtHumidity1', $this->Translate('External Humidity 2'), '~Humidity.F');
                            $this->SetValue('Shelly_ExtHumidity1', $Buffer->Payload);
                            break;
                        case 2:
                            $this->RegisterVariableFloat('Shelly_ExtHumidity2', $this->Translate('External Humidity 3'), '~Humidity.F');
                            $this->SetValue('Shelly_ExtHumidity2', $Buffer->Payload);
                            break;
                        case 1:
                            $this->RegisterVariableFloat('Shelly_ExtHumidity3', $this->Translate('External Humidity 4'), '~Humidity.F');
                            $this->SetValue('Shelly_ExtHumidity3', $Buffer->Payload);
                            break;
                        case 2:
                            $this->RegisterVariableFloat('Shelly_ExtHumidity4', $this->Translate('External Humidity 5'), '~Humidity.F');
                            $this->SetValue('Shelly_ExtHumidity4s', $Buffer->Payload);
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
            }
        }
    }

    private function SwitchMode(int $relay, bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/' . $relay . '/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}