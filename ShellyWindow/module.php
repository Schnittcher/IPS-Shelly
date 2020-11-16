<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/VariableProfileHelper.php';

class ShellyWindow extends IPSModule
{
    use Shelly;
    use VariableProfileHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('Device', '');
        $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Window');
        $this->RegisterVariableInteger('Shelly_Lux', $this->Translate('Lux'), '~Illumination');
        $this->RegisterVariableInteger('Shelly_Battery', $this->Translate('Battery'), '');

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

        if (($this->ReadPropertyString('Device') == 'DW2')) {
            $this->RegisterVariableFloat('Shelly_Temperature', $this->Translate('Temperature'), '~Temperature');
            $this->RegisterVariableBoolean('Shelly_Vibration', $this->Translate('Vibration'), '~Alert');
            $this->RegisterVariableInteger('Shelly_Tilt', $this->Translate('Tilt'), '');
        }
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');
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
                if (fnmatch('*/state', $Buffer->Topic)) {
                    $this->SendDebug('State Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'close':
                            $this->SetValue('Shelly_State', false);
                            break;
                        case 'open':
                            $this->SetValue('Shelly_State', true);
                            break;
                        default:
                            $this->SendDebug('Invalid Payload for State', $Buffer->Payload, 0);
                            break;
                        }
                }
                if (fnmatch('*/lux', $Buffer->Topic)) {
                    $this->SendDebug('Lux Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Lux', boolval($Buffer->Payload));
                }
                if (fnmatch('*/battery', $Buffer->Topic)) {
                    $this->SendDebug('Battery Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Battery', $Buffer->Payload);
                }
                if (fnmatch('*/temperature', $Buffer->Topic)) {
                    $this->SendDebug('Temperature Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Temperature', $Buffer->Payload);
                }
                if (fnmatch('*/vibration', $Buffer->Topic)) {
                    $this->SendDebug('Vibration Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 1:
                            $this->SetValue('Shelly_Vibration', true);
                            break;
                        case 0:
                            $this->SetValue('Shelly_Vibration', false);
                            break;
                        default:
                            $this->SendDebug('Invalid Payload for Vibration', $Buffer->Payload, 0);
                            break;
                        }
                }
                if (fnmatch('*/tilt', $Buffer->Topic)) {
                    $this->SendDebug('Tilt Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Tilt', $Buffer->Payload);
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
