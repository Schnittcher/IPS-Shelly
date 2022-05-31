<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyDimmer extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Shelly_Brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true],

        ['Shelly_Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],

        ['Shelly_Temperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_Overtemperature', 'Overtemperature', VARIABLETYPE_BOOLEAN, '', [], '', false, true],
        ['Shelly_Overload', 'Overload', VARIABLETYPE_BOOLEAN, '', [], '', false, true],
        ['Shelly_Loaderror', 'Loaderror', VARIABLETYPE_BOOLEAN, '', [], '', false, true],

        ['Shelly_Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Shelly_Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],

        ['Shelly_InputEvent0', 'Input 1 Event', VARIABLETYPE_INTEGER, 'Shelly.DimmerInput', [], '', false, true],
        ['Shelly_InputEvent1', 'Input 2 Event', VARIABLETYPE_INTEGER, 'Shelly.DimmerInput', [], '', false, true],

        ['Shelly_InputEventCount0', 'Input 1 Event Count', VARIABLETYPE_INTEGER, '', [], '', false, true],
        ['Shelly_InputEventCount1', 'Input 2 Event Count', VARIABLETYPE_INTEGER, '', [], '', false, true],

        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterProfileIntegerEx('Shelly.DimmerInput', 'ArrowRight', '', '', [
            [0, $this->Translate('shortpush'),  '', 0x08f26e],
            [1, $this->Translate('longpush'),  '', 0x06a94d]
        ]);
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
                    $this->SetValue('Shelly_Brightness', $Payload->brightness);
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
                if (fnmatch('*/input/[01]*', $Buffer->Topic)) {
                    $this->SendDebug('Input Payload', $Buffer->Payload, 0);
                    $ShellyTopic = explode('/', $Buffer->Topic);
                    $Key = count($ShellyTopic) - 1;
                    $index = $ShellyTopic[$Key];

                    switch ($Buffer->Payload) {
                        case 0:
                            $this->SetValue('Shelly_Input' . $index, false);
                            break;
                        case 1:
                            $this->SetValue('Shelly_Input' . $index, true);
                            break;
                    }
                }
                if (fnmatch('*/input_event/[01]*', $Buffer->Topic)) {
                    $this->SendDebug('Input Payload', $Buffer->Payload, 0);
                    $ShellyTopic = explode('/', $Buffer->Topic);
                    $Key = count($ShellyTopic) - 1;
                    $index = $ShellyTopic[$Key];

                    $Payload = json_decode($Buffer->Payload);
                    $this->SendDebug('Input Payload', $Buffer->Payload, 0);
                    switch ($Payload->event) {
                        case 'S':
                            $this->SetValue('Shelly_InputEvent' . $index, 0);
                            break;
                        case 'L':
                            $this->SetValue('Shelly_InputEvent' . $index, 1);
                            break;
                    }
                    $this->SetValue('Shelly_InputEventCount' . $index, $Payload->event_cnt);
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
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/light/0/set';
        $Payload['turn'] = 'off';
        if ($value > 0) {
            $Payload['brightness'] = strval($value);
            $Payload['turn'] = 'on';
            $Payload['transition'] = strval($transition);
        }

        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
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
}