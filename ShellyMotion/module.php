<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyMotion extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', [], '', false, true],
        ['Shelly_Illuminance', 'Illuminance', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true],
        ['Shelly_Vibration', 'Vibration', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/status', $Buffer->Topic)) {
                    $this->SendDebug('Status Payload', $Buffer->Payload, 0);
                    $Payload = json_decode($Buffer->Payload);
                    if (property_exists($Payload, 'motion')) {
                        $this->SetValue('Shelly_Motion', $Payload->motion);
                    }
                    if (property_exists($Buffer->Payload, 'active')) {
                        $this->SetValue('Shelly_Active', $Payload->active);
                    }
                    if (property_exists($Payload, 'vibration')) {
                        $this->SetValue('Shelly_Vibration', $Payload->vibration);
                    }
                    if (property_exists($Payload, 'lux')) {
                        $this->SetValue('Shelly_Illuminance', $Payload->lux);
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
