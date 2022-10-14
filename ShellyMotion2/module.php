<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyMotion2 extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', [], '', false, true],
        ['Shelly_Illuminance', 'Illuminance', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true],
        ['Shelly_Vibration', 'Vibration', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];
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
                if (fnmatch('*/status', $Buffer->Topic)) {
                    $this->SendDebug('Status Payload', $Buffer->Payload, 0);
                    $Payload = json_decode($Buffer->Payload);
                    if (property_exists($Payload, 'motion')) {
                        $this->SetValue('Shelly_Motion', $Payload->motion);
                    }
                    if (property_exists($Payload, 'vibration')) {
                        $this->SetValue('Shelly_Vibration', $Payload->vibration);
                    }
                    if (property_exists($Payload, 'lux')) {
                        $this->SetValue('Shelly_Illuminance', $Payload->lux);
                    }
                    if (property_exists($Payload, 'tmp')) {
                        if (property_exists($Payload, 'tmp')) {
                            $this->SetValue('Shelly_Temperature', $Payload->tmp->value);
                        }
                    }
                    if (property_exists($Payload, 'bat')) {
                        $this->SetValue('Shelly_Battery', $Payload->bat);
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
}
