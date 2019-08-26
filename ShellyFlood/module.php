<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class ShellyFlood extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
        $this->RegisterVariableBoolean('Shelly_Flood', $this->Translate('Flood'), '~Alert');
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
            $this->SendDebug('MQTT Payload', $Buffer->Payload, 0);

            if (property_exists($Buffer, 'Topic')) {
                if (fnmatch('*/sensor/temperature*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Temperature'), $Buffer->Payload);
                }
                if (fnmatch('*/sensor/flood*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Flood'), $Buffer->Payload);
                }
                if (fnmatch('*/sensor/battery*', $Buffer->Topic)) {
                    SetValue($this->GetIDForIdent('Shelly_Battery'), $Buffer->Payload);
                }
            }
        }
    }
}
