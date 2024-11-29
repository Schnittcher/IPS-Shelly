<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModuleBLU.php';

class ShellyBLURCButton4 extends ShellyModuleBLU
{
    public static $Variables = [
        ['Shelly_Button', 'Button 1', VARIABLETYPE_INTEGER, 'ShellyBLU.Button', [], '', false, true],
        ['Shelly_Button1', 'Button 2', VARIABLETYPE_INTEGER, 'ShellyBLU.Button', [], '', false, true],
        ['Shelly_Button2', 'Button 3', VARIABLETYPE_INTEGER, 'ShellyBLU.Button', [], '', false, true],
        ['Shelly_Button3', 'Button 4', VARIABLETYPE_INTEGER, 'ShellyBLU.Button', [], '', false, true],
        ['Shelly_RSSI', 'RSSI', VARIABLETYPE_INTEGER, '', [], '', false, true],
        ['Shelly_Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true]
    ];

    public function Create()
    {
        parent::Create();
        if (!IPS_VariableProfileExists('ShellyBLU.Button')) {
            $this->RegisterProfileIntegerEx('ShellyBLU.Button', '', '', '', [
                [1, $this->Translate('Single'), '', -1],
                [2, $this->Translate('Double'), '', -1],
                [3, $this->Translate('Tripple'), '', -1],
                [4, $this->Translate('Long'), '', -1]
            ]);
        }
    }

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
                                if (property_exists($Payload->params->events[0]->data, 'button')) {
                                    $this->SetValue('Shelly_Button', $Payload->params->events[0]->data->button[0]);
                                    $this->SetValue('Shelly_Button1', $Payload->params->events[0]->data->button[1]);
                                    $this->SetValue('Shelly_Button2', $Payload->params->events[0]->data->button[2]);
                                    $this->SetValue('Shelly_Button3', $Payload->params->events[0]->data->button[3]);
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
