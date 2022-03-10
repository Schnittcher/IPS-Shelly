<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyPlus1 extends IPSModule
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
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        $this->RegisterVariableBoolean('State', $this->Translate('State'), '~Switch', 0);
        $this->EnableAction('State');
        $this->MaintainVariable('Power', $this->Translate('Power'), 2, '~Watt.3680', 2, $this->ReadPropertyString('Device') == 'shellyplus1pm');
        $this->MaintainVariable('TotalEnergy', $this->Translate('Total Energy'), 2, '~Electricity', 2, $this->ReadPropertyString('Device') == 'shellyplus1pm');
        $this->MaintainVariable('Current', $this->Translate('Current'), 2, '~Ampere', 3, $this->ReadPropertyString('Device') == 'shellyplus1pm');
        $this->MaintainVariable('Voltage', $this->Translate('Voltage'), 2, '~Volt.230', 4, $this->ReadPropertyString('Device') == 'shellyplus1pm');
        $this->RegisterVariableBoolean('Overtemp', $this->Translate('Overtemp'), '~Alert', 5);
        $this->RegisterVariableBoolean('Overpower', $this->Translate('Overpower'), '~Alert', 6);
        $this->RegisterVariableBoolean('Overvoltage', $this->Translate('Overvoltage'), '~Alert', 7);
        $this->RegisterVariableString('EventComponent', $this->Translate('Event Component'), '', 8);
        $this->RegisterVariableString('Event', $this->Translate('Event'), '', 9);

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
        $this->RegisterVariableBoolean('Reachable', $this->Translate('Reachable'), 'Shelly.Reachable', 150);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State':
                $this->SwitchMode(0, $Value);
                break;
            }
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
                        if (array_key_exists('switch:0', $Payload['params'])) {
                            $switch = $Payload['params']['switch:0'];
                            if (array_key_exists('output', $switch)) {
                                $this->SetValue('State', $switch['output']);
                            }
                            if (array_key_exists('apower', $switch)) {
                                $this->SetValue('Power', $switch['apower']);
                            }
                            if (array_key_exists('voltage', $switch)) {
                                $this->SetValue('Voltage', $switch['voltage']);
                            }
                            if (array_key_exists('current', $switch)) {
                                $this->SetValue('Current', $switch['current']);
                            }
                            if (array_key_exists('aenergy', $switch)) {
                                if (array_key_exists('total', $switch['aenergy'])) {
                                    $this->SetValue('TotalEnergy', $switch['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('events', $Payload)) {
                                $events = $Payload['events'];
                                $this->SetValue('EventComponent', $events['component']);
                                $this->SetValue('Event', $events['event']);
                            }
                            if (array_key_exists('errors', $switch)) {
                                $this->SetValue('Overtemp', false);
                                $this->SetValue('Overpower', false);
                                $this->SetValue('Overvoltage', false);
                                $errors = '';
                                foreach ($switch['errors'] as $key => $error) {
                                    switch ($error) {
                                            case 'overtemp':
                                                $this->SetValue('Overtemp', true);
                                                break;
                                            case 'overpower':
                                                $this->SetValue('Overpower', true);
                                                break;
                                            case 'Overvoltage':
                                                $this->SetValue('Overvoltage', true);
                                                break;
                                            default:
                                                $this->LogMessage('Missing Variable for Error State "' . $error . '"', KL_ERROR);
                                                break;
                                        }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function SwitchMode(int $switch, bool $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $switch, 'on' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}
