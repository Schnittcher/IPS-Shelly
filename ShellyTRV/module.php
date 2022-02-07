<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyTRV extends IPSModule
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

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);

        $this->RegisterVariableBoolean('Shelly_Reachable', $this->Translate('Reachable'), 'Shelly.Reachable');

        $this->RegisterVariableInteger('Position', $this->Translate('Position'), '~Intensity.100', 1);
        $this->RegisterVariableFloat('TargetTemperature', $this->Translate('Target Temperature'), '~Temperature.Room', 2);
        $this->EnableAction('TargetTemperature');
        $this->RegisterVariableFloat('Temperature', $this->Translate('Temperature'), '~Temperature', 3);
        $this->RegisterVariableBoolean('Schedule', $this->Translate('Schedule'), '~Switch', 4);
        $this->EnableAction('Schedule');
        $this->RegisterVariableInteger('ScheduleProfile', $this->Translate('Schedule Profile'), '', 5);
        $this->EnableAction('ScheduleProfile');

        $this->RegisterVariableInteger('BatteryValue', $this->Translate('Battery'), '~Battery.100', 6);
        $this->RegisterVariableFloat('BatteryVoltage', $this->Translate('Battery Voltage'), '~Volt', 7);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . MQTT_GROUP_TOPIC . '/' . $MQTTTopic . '.*');
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'TargetTemperature':
                $this->setTargetTemp($Value);
                break;
            case 'Schedule':
                $this->setSchedule($Value);
                break;
            case 'ScheduleProfile':
                $this->setScheduleProfile($Value);
                break;
            default:
                $this->SendDebug('ReqestAction :: Invalid Ident', $ident, 0);
                break;
            }
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('ReceiveData :: JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            $Payload = json_decode($data->Payload);
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
            $this->SendDebug('ReceiveData :: MQTT Topic', $Buffer->Topic, 0);

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*info*', $Buffer->Topic)) {
                    if (property_exists($Payload, 'thermostats')) {
                        if (property_exists($Payload->thermostats[0], 'pos')) {
                            $this->SetValue('Position', $Payload->thermostats[0]->pos);
                        }
                        if (property_exists($Payload->thermostats[0], 'target_t')) {
                            $this->SetValue('TargetTemperature', $Payload->thermostats[0]->target_t->value);
                        }
                        if (property_exists($Payload->thermostats[0], 'tmp')) {
                            $this->SetValue('Temperature', $Payload->thermostats[0]->tmp->value);
                        }
                        if (property_exists($Payload->thermostats[0], 'schedule')) {
                            $this->SetValue('Schedule', $Payload->thermostats[0]->schedule);
                        }
                        if (property_exists($Payload->thermostats[0], 'schedule_profile')) {
                            $this->SetValue('ScheduleProfile', $Payload->thermostats[0]->schedule_profile);
                        }
                    }
                    if (property_exists($Payload, 'bat')) {
                        if (property_exists($Payload->bat[0], 'value')) {
                            $this->SetValue('BatteryValue', $Payload->bat[0]->value);
                        }
                        if (property_exists($Payload->bat[0], 'value')) {
                            $this->SetValue('BatteryVoltage', $Payload->bat[0]->voltage);
                        }
                    }
                }
            }
        }
    }

    private function setTargetTemp(float $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/target_t';
        $Payload = strval($Value);
        $this->sendMQTT($Topic, $Payload);
    }

    private function setSchedule(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/schedule';
        $Payload = strval(intval($Value));
        $this->sendMQTT($Topic, $Payload);
    }

    private function setScheduleProfile(int $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/schedule_profile';
        $Payload = strval($Value);
        $this->sendMQTT($Topic, $Payload);
    }
}