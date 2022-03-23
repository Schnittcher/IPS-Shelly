<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyPlusi4 extends IPSModule
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
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        $this->RegisterVariableBoolean('Input0', $this->Translate('Input') . ' 1', '~Switch');
        $this->RegisterVariableBoolean('Input1', $this->Translate('Input') . ' 2', '~Switch');
        $this->RegisterVariableBoolean('Input2', $this->Translate('Input') . ' 3', '~Switch');
        $this->RegisterVariableBoolean('Input3', $this->Translate('Input') . ' 4', '~Switch');

        $this->RegisterVariableString('EventComponent', $this->Translate('Event Component'), '', 1);
        $this->RegisterVariableString('Event', $this->Translate('Event'), '', 2);

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
        $this->RegisterVariableBoolean('Reachable', $this->Translate('Reachable'), 'Shelly.Reachable', 150);
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString, true);

            switch ($data['DataID']) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data['Buffer']);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
            }

            $this->SendDebug('MQTT Topic', $Buffer['Topic'], 0);

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
                        }
                        for ($i = 0; $i <= 3; $i++) {
                            $inputIndex = 'input:' . $i;
                            if (array_key_exists($inputIndex, $Payload['params'])) {
                                $input = $Payload['params'][$inputIndex];
                                if (array_key_exists('state', $input)) {
                                    $this->SetValue('Input' . $i, $input['state']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
