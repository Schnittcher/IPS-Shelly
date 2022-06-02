<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyEM extends ShellyModule
{
    public static $Variables = [
        ['Shelly_State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],

        ['Shelly_Power0', 'Power L1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy0', 'Energy L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_ReturnedEnergy0', 'Returned Energy L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_ReactivePower0', 'Reactive Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Voltage0', 'Voltage L1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Total0', 'Total L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_TotalReturned0', 'Total Returned L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_Power1', 'Power L2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Energy1', 'Energy L2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_ReturnedEnergy1', 'Returned Energy L2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_ReactivePower1', 'Reactive Power L2', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Voltage1', 'Voltage L2', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Total1', 'Total L2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_TotalReturned1', 'Total Returned L2', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Shelly_PowerFactor0', 'Power Factor L1', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true],
        ['Shelly_Current0', 'Current L1', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['Shelly_Voltage0', 'Voltage L1', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Shelly_Total0', 'Total L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['Shelly_TotalReturned0', 'Total Returned L1', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
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

            //Power Variable prüfen
            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Relay Payload', $Buffer->Payload, 0);
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
                //Emter 0
                if (fnmatch('*emeter/0/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Energy0', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/0/returned_energy', $Buffer->Topic)) {
                    $this->SendDebug('Returned Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReturnedEnergy0', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power0', $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/reactive_power', $Buffer->Topic)) {
                    $this->SendDebug('Reactive Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReactivePower0', $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage0', $Buffer->Payload);
                }
                if (fnmatch('*emeter/0/total', $Buffer->Topic)) {
                    $this->SendDebug('Total Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Total0', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/0/total_returned', $Buffer->Topic)) {
                    $this->SendDebug('Total Returned Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_TotalReturned0', floatval($Buffer->Payload) / 1000);
                }

                //Emter 1
                if (fnmatch('*emeter/1/energy', $Buffer->Topic)) {
                    $this->SendDebug('Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Energy1', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/1/returned_energy', $Buffer->Topic)) {
                    $this->SendDebug('Returned Energy Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReturnedEnergy1', floatval($Buffer->Payload) / 60000);
                }
                if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
                    $this->SendDebug('Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power1', $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/reactive_power', $Buffer->Topic)) {
                    $this->SendDebug('Reactive Power Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_ReactivePower1', $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage1', $Buffer->Payload);
                }
                if (fnmatch('*emeter/1/total', $Buffer->Topic)) {
                    $this->SendDebug('Total Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Total1', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/1/total_returned', $Buffer->Topic)) {
                    $this->SendDebug('Total Returned Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_TotalReturned1', floatval($Buffer->Payload) / 1000);
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
