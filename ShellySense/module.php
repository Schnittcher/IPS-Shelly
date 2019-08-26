<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class ShellySense extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterVariableBoolean('Shelly_Motion', $this->Translate('Motion'), '~Motion');
        $this->RegisterVariableBoolean('Shelly_Charger', $this->Translate('External Charger'), '~Switch');
        $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
        $this->RegisterVariableFloat('Shelly_Humidity', $this->Translate('Humidity'), '~Humidity.F');
        $this->RegisterVariableInteger('Shelly_Lux', $this->Translate('Lux'), '~Illumination');
        $this->RegisterVariableInteger('Shelly_Battery', $this->Translate('Battery'), '~Battery.100');
        $this->RegisterPropertyString('MQTTTopic', '');
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

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $Buffer = $data;
            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/sensor/motion*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            SetValue($this->GetIDForIdent('Shelly_Motion'), true);
                            break;
                        case 'false':
                            SetValue($this->GetIDForIdent('Shelly_Motion'), false);
                            break;
                        default:
                            $this->SendDebug('Motion Sensor', 'Undefined Payload: ' . $Buffer->Payload, 0);
                            break;
                    }
                }
                if (fnmatch('*/sensor/charger*', $Buffer->Topic)) {
                    switch ($Buffer->Payload) {
                        case 'true':
                            SetValue($this->GetIDForIdent('Shelly_Charger'), true);
                            break;
                        case 'false':
                            SetValue($this->GetIDForIdent('Shelly_Charger'), false);
                            break;
                        default:
                            $this->SendDebug('External Charger', 'Undefined Payload: ' . $Buffer->Payload, 0);
                            break;
                    }
                }
                if (fnmatch('*/sensor/temperature*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Temperature'), $Buffer->Payload);
                }
                if (fnmatch('*/sensor/humidity*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Humidity'), $Buffer->Payload);
                }
                if (fnmatch('*/sensor/lux*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Lux'), $Buffer->Payload);
                }
                if (fnmatch('*/sensor/battery*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Battery'), $Buffer->Payload);
                }
            }
        }
    }
}
