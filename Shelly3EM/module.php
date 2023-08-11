<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class Shelly3EM extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],

        ['Shelly_Power0', 'Power L1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_PowerFactor0', 'Power Factor L1', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Shelly_Current0', 'Current L1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Shelly_Voltage0', 'Voltage L1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Total0', 'Total L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_TotalReturned0', 'Total Returned L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_Power1', 'Power L2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_PowerFactor1', 'Power Factor L2', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Shelly_Current1', 'Current L2', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Shelly_Voltage1', 'Voltage L2', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Total1', 'Total L2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_TotalReturned1', 'Total Returned L2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_Power2', 'Power L3', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_PowerFactor2', 'Power Factor L3', VARIABLETYPE_FLOAT, '', [], '', false, true],
        ['Shelly_Current2', 'Current L3', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Shelly_Voltage2', 'Voltage L3', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Total2', 'Total L3', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_TotalReturned2', 'Total Returned L3', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_CurrentN', 'N Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Shelly_IxsumN', 'Neutral Conductor N RMS (ixsum)', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],

    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_State':
                $this->SwitchMode(0, $Value);
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
                    //Power prüfen und in IPS setzen
                    switch ($Buffer->Payload) {
                        case 'off':
                            $this->SetValue('Shelly_State', 0);
                            break;
                        case 'on':
                            $this->SetValue('Shelly_State', 1);
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

                //Phase A
                if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Power0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/pf', $Buffer->Topic)) {
                    $this->SetValue('Shelly_PowerFactor0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/current', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Current0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/voltage', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Voltage0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/total', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Total0', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/0/total_returned', $Buffer->Topic)) {
                    $this->SetValue('Shelly_TotalReturned0', floatval($Buffer->Payload) / 1000);
                }

                //Phase B
                if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Power1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/pf', $Buffer->Topic)) {
                    $this->SetValue('Shelly_PowerFactor1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/current', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Current1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/voltage', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Voltage1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/total', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Total1', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/1/total_returned', $Buffer->Topic)) {
                    $this->SetValue('Shelly_TotalReturned1', floatval($Buffer->Payload) / 1000);
                }

                //Phase C
                if (fnmatch('*emeter/2/power', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Power2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/pf', $Buffer->Topic)) {
                    $this->SetValue('Shelly_PowerFactor2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/current', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Current2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/voltage', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Voltage2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/total', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Total2', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/2/total_returned', $Buffer->Topic)) {
                    $this->SetValue('Shelly_TotalReturned2', floatval($Buffer->Payload) / 1000);
                }

                //Emeter N
                if (fnmatch('*emeter_n/current', $Buffer->Topic)) {
                    $this->SetValue('Shelly_CurrentN', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter_n/ixsum', $Buffer->Topic)) {
                    $this->SetValue('Shelly_IxsumN', floatval($Buffer->Payload));
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
