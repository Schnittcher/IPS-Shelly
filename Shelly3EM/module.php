<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class Shelly3EM extends IPSModule
{
    use Shelly;
    use VariableProfileHelper;
    use MQTTHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');

        $this->RegisterVariableFloat('Shelly_Power0', $this->Translate('Power') . ' L1', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_PowerFactor0', $this->Translate('Power Factor') . ' L1', '');
        $this->RegisterVariableFloat('Shelly_Current0', $this->Translate('Current') . ' L1', '~Ampere');
        $this->RegisterVariableFloat('Shelly_Voltage0', $this->Translate('Voltage') . ' L1', '~Volt');
        $this->RegisterVariableFloat('Shelly_Total0', $this->Translate('Total') . ' L1', '~Electricity');
        $this->RegisterVariableFloat('Shelly_TotalReturned0', $this->Translate('Total Returned') . ' L1', '~Electricity');

        $this->RegisterVariableFloat('Shelly_Power1', $this->Translate('Power') . ' L2', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_PowerFactor1', $this->Translate('Power Factor') . ' L2', '');
        $this->RegisterVariableFloat('Shelly_Current1', $this->Translate('Current') . ' L2', '~Ampere');
        $this->RegisterVariableFloat('Shelly_Voltage1', $this->Translate('Voltage') . ' L2', '~Volt');
        $this->RegisterVariableFloat('Shelly_Total1', $this->Translate('Total') . ' L2', '~Electricity');
        $this->RegisterVariableFloat('Shelly_TotalReturned1', $this->Translate('Total Returned') . ' L2', '~Electricity');

        $this->RegisterVariableFloat('Shelly_Power2', $this->Translate('Power') . ' L3', '~Watt.3680');
        $this->RegisterVariableFloat('Shelly_PowerFactor2', $this->Translate('Power Factor') . ' L3', '');
        $this->RegisterVariableFloat('Shelly_Current2', $this->Translate('Current') . ' L3', '~Ampere');
        $this->RegisterVariableFloat('Shelly_Voltage2', $this->Translate('Voltage') . ' L3', '~Volt');
        $this->RegisterVariableFloat('Shelly_Total2', $this->Translate('Total') . ' L3', '~Electricity');
        $this->RegisterVariableFloat('Shelly_TotalReturned2', $this->Translate('Total Returned') . ' L3', '~Electricity');

        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');
        $this->EnableAction('Shelly_State');

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);

        $this->RegisterVariableBoolean('Shelly_Reachable', $this->Translate('Reachable'), 'Shelly.Reachable');
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
                $this->SwitchMode(0, $Value);
                break;
            case 'Shelly_State2':
                $this->SwitchMode(1, $Value);
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
                if (fnmatch('*/relay/0', $Buffer->Topic)) {
                    $this->SendDebug('Relay 0 Payload', $Buffer->Payload, 0);
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

                //Phase A
                if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
                    $this->SendDebug('Power L1 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/pf', $Buffer->Topic)) {
                    $this->SendDebug('Power Factor L1 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_PowerFactor0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/current', $Buffer->Topic)) {
                    $this->SendDebug('Current L1 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Current0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage L1 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage0', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/0/total', $Buffer->Topic)) {
                    $this->SendDebug('Total L1 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Total0', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/0/total_returned', $Buffer->Topic)) {
                    $this->SendDebug('Total Returned L1 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_TotalReturned0', floatval($Buffer->Payload) / 1000);
                }

                //Phase B
                if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
                    $this->SendDebug('Power L2 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/pf', $Buffer->Topic)) {
                    $this->SendDebug('Power Factor L2 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_PowerFactor1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/current', $Buffer->Topic)) {
                    $this->SendDebug('Current L2 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Current1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage L2 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage1', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/1/total', $Buffer->Topic)) {
                    $this->SendDebug('Total L2 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Total1', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/1/total_returned', $Buffer->Topic)) {
                    $this->SendDebug('Total Returned L2 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_TotalReturned1', floatval($Buffer->Payload) / 1000);
                }

                //Phase C
                if (fnmatch('*emeter/2/power', $Buffer->Topic)) {
                    $this->SendDebug('Power L3 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Power2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/pf', $Buffer->Topic)) {
                    $this->SendDebug('Power Factor L3 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_PowerFactor2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/current', $Buffer->Topic)) {
                    $this->SendDebug('Current L3 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Current2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/voltage', $Buffer->Topic)) {
                    $this->SendDebug('Voltage L3 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Voltage2', floatval($Buffer->Payload));
                }
                if (fnmatch('*emeter/2/total', $Buffer->Topic)) {
                    $this->SendDebug('Total L3 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Total2', floatval($Buffer->Payload) / 1000);
                }
                if (fnmatch('*emeter/2/total_returned', $Buffer->Topic)) {
                    $this->SendDebug('Total ReturnedL 3 Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_TotalReturned2', floatval($Buffer->Payload) / 1000);
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
