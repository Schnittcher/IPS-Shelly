<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyMotion2 extends ShellyModule
{
    public static $Variables = [
        ['Shelly_Motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', [], '', false, true, false],
        ['Shelly_Illuminance', 'Illuminance', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true, false],
        ['Shelly_Vibration', 'Vibration', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true, false],
        ['Shelly_Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];
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
                if (fnmatch('*/status', $Buffer->Topic)) {
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
}
