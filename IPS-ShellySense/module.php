<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_ShellySense extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{EE0D345A-CF31-428A-A613-33CE98E752DD}');

        $this->RegisterVariableBoolean('Shelly_Motion', 'Motion', '~Motion');
        $this->RegisterVariableBoolean('Shelly_Charger', 'External Charger', '~Switch');
        $this->RegisterVariableFloat('Shelly_Temperature', 'Temperature', '~Temperature');
        $this->RegisterVariableFloat('Shelly_Humidity', 'Humidity', '~Humidity.F');
        $this->RegisterVariableInteger('Shelly_Lux', 'Lux', '~Illumination');
        $this->RegisterVariableInteger('Shelly_Battery', 'Battery', '~Battery.100');
        $this->RegisterPropertyString('MQTTTopic', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{EE0D345A-CF31-428A-A613-33CE98E752DD}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $Buffer = json_decode($data->Buffer);
            $this->SendDebug('MQTT Topic', $Buffer->TOPIC, 0);

            if (property_exists($Buffer, 'TOPIC')) {
                if (fnmatch('*/sensor/motion*', $Buffer->TOPIC)) {
                    switch ($Buffer->MSG) {
                        case 'true':
                            SetValue($this->GetIDForIdent('Shelly_Motion'), true);
                            break;
                        case 'false':
                            SetValue($this->GetIDForIdent('Shelly_Motion'), false);
                            break;
                        default:
                            $this->SendDebug('Motion Sensor', 'Undefined MSG: ' . $Buffer->MSG, 0);
                            break;
                    }
                }
                if (fnmatch('*/sensor/charger*', $Buffer->TOPIC)) {
                    switch ($Buffer->MSG) {
                        case 'true':
                            SetValue($this->GetIDForIdent('Shelly_Charger'), true);
                            break;
                        case 'false':
                            SetValue($this->GetIDForIdent('Shelly_Charger'), false);
                            break;
                        default:
                            $this->SendDebug('External Charger', 'Undefined MSG: ' . $Buffer->MSG, 0);
                            break;
                    }
                }
                if (fnmatch('*/sensor/temperature*', $Buffer->TOPIC)) {
                    SetValue($this->GetIDForIdent('Shelly_Temperature'), $Buffer->MSG);
                }
                if (fnmatch('*/sensor/humidity*', $Buffer->TOPIC)) {
                    SetValue($this->GetIDForIdent('Shelly_Humidity'), $Buffer->MSG);
                }
                if (fnmatch('*/sensor/lux*', $Buffer->TOPIC)) {
                    SetValue($this->GetIDForIdent('Shelly_Lux'), $Buffer->MSG);
                }
                if (fnmatch('*/sensor/battery*', $Buffer->TOPIC)) {
                    SetValue($this->GetIDForIdent('Shelly_Battery'), $Buffer->MSG);
                }
            }
        }
    }
}
