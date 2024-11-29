<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyTRV extends ShellyModule
{
    public static $Variables = [
        ['Position', 'Position', VARIABLETYPE_INTEGER, '~Intensity.100', [], '', true, true, false],
        ['BoostMinutes', 'Boost Minutes', VARIABLETYPE_INTEGER, '', [], '', true, true, false],
        ['TargetTemperature', 'Target Temperature', VARIABLETYPE_FLOAT, '~Temperature.Room', [], '', true, true, false],
        ['Temperature', 'Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],
        ['ExtTemperature', 'External Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', true, true, false],
        ['Schedule', 'Schedule', VARIABLETYPE_BOOLEAN, '~Switch', '', '', true, true, false],
        ['ScheduleProfile', 'Schedule Profile', VARIABLETYPE_INTEGER, '', '', '', true, true, false],
        ['BatteryValue', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true, false],
        ['BatteryVoltage', 'Battery Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['WindowOpen', 'Window', VARIABLETYPE_BOOLEAN, '~Window', [], '', true, true, false],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', [], '', false, true, false]

    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'TargetTemperature':
                $this->setTargetTemp($Value);
                break;
            case 'Position':
                $this->setValvePosition($Value);
                break;
            case 'BoostMinutes':
                $this->setBoostMinutes($Value);
                break;
            case 'ExtTemperature':
                $this->setExtTemp($Value);
                break;
            case 'Schedule':
                $this->setSchedule($Value);
                break;
            case 'ScheduleProfile':
                $this->setScheduleProfile($Value);
                break;
            case 'WindowOpen':
                $this->setWindowOpen($Value);
                break;
            default:
                $this->SendDebug('ReqestAction :: Invalid Ident', $ident, 0);
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

            $Payload = json_decode($Buffer->Payload);
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
                        if (property_exists($Payload->thermostats[0], 'window_open')) {
                            $this->SetValue('WindowOpen', $Payload->thermostats[0]->window_open);
                        }
                    }
                    if (property_exists($Payload, 'bat')) {
                        if (property_exists($Payload->bat, 'value')) {
                            $this->SetValue('BatteryValue', $Payload->bat->value);
                        }
                        if (property_exists($Payload->bat, 'voltage')) {
                            $this->SetValue('BatteryVoltage', $Payload->bat->voltage);
                        }
                    }
                }
                if (fnmatch('*online', $Buffer->Topic)) {
                    $this->SetValue('Shelly_Reachable', $Payload);
                    if (!$Payload) {
                        $this->zeroingValues();
                    }
                }
            }
        }
    }

    private function setTargetTemp(float $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/target_t';
        $Payload = strval(number_format($Value, 1, '.', ''));
        $this->sendMQTT($Topic, $Payload);
    }

    private function setValvePosition(int $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/valve_pos';
        $Payload = strval(intval($Value));
        $this->sendMQTT($Topic, $Payload);
    }

    private function setBoostMinutes(int $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/boost_minutes';
        $Payload = strval(intval($Value));
        $this->sendMQTT($Topic, $Payload);
    }

    private function setExtTemp(float $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/ext_t';
        $Payload = strval(number_format($Value, 1, '.', ''));
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

    private function setWindowOpen(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/thermostat/0/command/window_open';
        if ($Value) {
            $Payload = 'open';
        } else {
            $Payload = 'close';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}