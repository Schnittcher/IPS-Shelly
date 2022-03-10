<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyMotion extends IPSModule
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
        $this->RegisterPropertyString('Device', '');
        $this->RegisterVariableBoolean('Shelly_Motion', $this->Translate('Motion'), '~Motion');
        $this->RegisterVariableInteger('Shelly_Illuminance', $this->Translate('Illuminance'), '~Illumination');
        $this->RegisterVariableBoolean('Shelly_Vibration', $this->Translate('Vibration'), '~Alert');
        $this->RegisterVariableInteger('Shelly_Battery', $this->Translate('Battery'), '~Battery.100');

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
        $this->SetReceiveDataFilter('.*' . MQTT_GROUP_TOPIC . '/' . $MQTTTopic . '.*');
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
                if (fnmatch('*/status', $Buffer->Topic)) {
                    $this->SendDebug('Status Payload', $Buffer->Payload, 0);
                    $Payload = json_decode($Buffer->Payload);
                    if (property_exists($Payload, 'motion')) {
                        $this->SetValue('Shelly_Motion', $Payload->motion);
                    }
                    if (property_exists($Buffer->Payload, 'active')) {
                        $this->SetValue('Shelly_Active', $Payload->active);
                    }
                    if (property_exists($Payload, 'vibration')) {
                        $this->SetValue('Shelly_Vibration', $Payload->vibration);
                    }
                    if (property_exists($Payload, 'lux')) {
                        $this->SetValue('Shelly_Illuminance', $Payload->lux);
                    }
                    if (property_exists($Payload, 'bat')) {
                        $this->SetValue('Shelly_Battery', $Payload->bat);
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
            }
        }
    }
}
