<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModuleBLU.php';

class ShellyBLUMotion extends ShellyModuleBLU
{
    public static $Variables = [
        ['Shelly_Motion', 'Motion', VARIABLETYPE_BOOLEAN, '~Motion', [], '', false, true],
        ['Shelly_RSSI', 'RSSI', VARIABLETYPE_INTEGER, '', [], '', false, true],
        ['Shelly_Illuminance', 'Illuminance', VARIABLETYPE_INTEGER, '~Illumination', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('BLUAddress'))) {
            $Buffer = json_decode($JSONString);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer->Payload = utf8_decode($Buffer->Payload);
            }

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/events/rpc', $Buffer->Topic)) {
                    $Payload = json_decode($Buffer->Payload);
                    if (property_exists($Payload, 'params')) {
                        if (property_exists($Payload->params, 'events')) {
                            if (property_exists($Payload->params->events[0], 'data')) {
                                if (property_exists($Payload->params->events[0]->data, 'battery')) {
                                    $this->SetValue('Shelly_Battery', $Payload->params->events[0]->data->battery);
                                }
                                if (property_exists($Payload->params->events[0]->data, 'motion')) {
                                    $this->SetValue('Shelly_Motion', boolval($Payload->params->events[0]->data->motion));
                                }
                                if (property_exists($Payload->params->events[0]->data, 'illuminance')) {
                                    $this->SetValue('Shelly_Illuminance', $Payload->params->events[0]->data->illuminance);
                                }
                                if (property_exists($Payload->params->events[0]->data, 'rssi')) {
                                    $this->SetValue('Shelly_RSSI', intval($Payload->params->events[0]->data->rssi));
                                }
                            }
                        }
                    }
                    if (property_exists($Buffer->Payload, 'active')) {
                    }
                }
            }
        }
    }
}
