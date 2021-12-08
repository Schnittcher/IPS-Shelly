<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyPro4PM extends IPSModule
{
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

        $this->RegisterVariableBoolean('State0', $this->Translate('State'), '~Switch', 0);
        $this->EnableAction('State0');
        $this->RegisterVariableFloat('Power0', $this->Translate('Power'), '~Watt.3680', 1);
        $this->RegisterVariableFloat('TotalEnergy0', $this->Translate('Total consumption'), '~Electricity', 2);
        $this->RegisterVariableFloat('Current0', $this->Translate('Current'), '~Ampere', 3);
        $this->RegisterVariableFloat('Voltage0', $this->Translate('Voltage'), '~Volt.230', 4);
        $this->RegisterVariableBoolean('Overtemp0', $this->Translate('Overtemp'), '~Alert', 5);
        $this->RegisterVariableBoolean('Overpower0', $this->Translate('Overpower'), '~Alert', 6);
        $this->RegisterVariableBoolean('Overvoltage0', $this->Translate('Overvoltage'), '~Alert', 7);

        $this->RegisterVariableBoolean('State1', $this->Translate('State') . ' 2', '~Switch', 10);
        $this->EnableAction('State1');
        $this->RegisterVariableFloat('Power1', $this->Translate('Power') . ' 2', '~Watt.3680', 11);
        $this->RegisterVariableFloat('TotalEnergy1', $this->Translate('Total consumption') . ' 2', '~Electricity', 12);
        $this->RegisterVariableFloat('Current1', $this->Translate('Current') . ' 2', '~Ampere', 13);
        $this->RegisterVariableFloat('Voltage1', $this->Translate('Voltage') . ' 2', '~Volt.230', 14);
        $this->RegisterVariableBoolean('Overtemp1', $this->Translate('Overtemp') . ' 2', '~Alert', 15);
        $this->RegisterVariableBoolean('Overpower1', $this->Translate('Overpower') . ' 2', '~Alert', 16);
        $this->RegisterVariableBoolean('Overvoltage1', $this->Translate('Overvoltage') . ' 2', '~Alert', 17);

        $this->RegisterVariableBoolean('State2', $this->Translate('State') . ' 3', '~Switch', 20);
        $this->EnableAction('State2');
        $this->RegisterVariableFloat('Power2', $this->Translate('Power') . ' 3', '~Watt.3680', 21);
        $this->RegisterVariableFloat('TotalEnergy2', $this->Translate('Total consumption') . ' 3', '~Electricity', 22);
        $this->RegisterVariableFloat('Current2', $this->Translate('Current') . ' 3', '~Ampere', 23);
        $this->RegisterVariableFloat('Voltage2', $this->Translate('Voltage') . ' 3', '~Volt.230', 24);
        $this->RegisterVariableBoolean('Overtemp2', $this->Translate('Overtemp') . ' 3', '~Alert', 25);
        $this->RegisterVariableBoolean('Overpower2', $this->Translate('Overpower') . ' 3', '~Alert', 26);
        $this->RegisterVariableBoolean('Overvoltage2', $this->Translate('Overvoltage') . ' 3', '~Alert', 27);

        $this->RegisterVariableBoolean('State3', $this->Translate('State') . ' 4', '~Switch', 30);
        $this->EnableAction('State3');
        $this->RegisterVariableFloat('Power3', $this->Translate('Power') . ' 4', '~Watt.3680', 31);
        $this->RegisterVariableFloat('TotalEnergy3', $this->Translate('Total consumption') . ' 4', '~Electricity', 32);
        $this->RegisterVariableFloat('Current3', $this->Translate('Current') . ' 4', '~Ampere', 33);
        $this->RegisterVariableFloat('Voltage3', $this->Translate('Voltage') . ' 4', '~Volt.230', 34);
        $this->RegisterVariableBoolean('Overtemp4', $this->Translate('Overtemp') . ' 4', '~Alert', 35);
        $this->RegisterVariableBoolean('Overpower4', $this->Translate('Overpower') . ' 4', '~Alert', 36);
        $this->RegisterVariableBoolean('Overvoltage4', $this->Translate('Overvoltage') . ' 4', '~Alert', 37);

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);

        $this->RegisterVariableBoolean('Reachable', $this->Translate('Reachable'), 'Shelly.Reachable', 150);
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'State0':
                $this->SwitchMode(0, $Value);
                break;
            case 'State1':
                $this->SwitchMode(1, $Value);
                break;
            case 'State2':
                $this->SwitchMode(2, $Value);
                break;
            case 'State3':
                $this->SwitchMode(3, $Value);
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
                        for ($i = 0; $i <= 3; $i++) {
                            $switchIndex = 'switch:' . $i;
                            if (array_key_exists($switchIndex, $Payload['params'])) {
                                $switch = $Payload['params'][$switchIndex];
                                if (array_key_exists('output', $switch)) {
                                    $this->SetValue('State' . $i, $switch['output']);
                                }
                                if (array_key_exists('apower', $switch)) {
                                    $this->SetValue('Power' . $i, $switch['apower']);
                                }
                                if (array_key_exists('voltage', $switch)) {
                                    $this->SetValue('Voltage' . $i, $switch['voltage']);
                                }
                                if (array_key_exists('current', $switch)) {
                                    $this->SetValue('Current' . $i, $switch['current']);
                                }
                                if (array_key_exists('aenergy', $switch)) {
                                    if (array_key_exists('total', $switch['aenergy'])) {
                                        $this->SetValue('TotalEnergy' . $i, $switch['aenergy']['total'] / 60000);
                                    }
                                }
                                if (array_key_exists('errors', $switch)) {
                                    $this->SetValue('Overtemp' . $i, false);
                                    $this->SetValue('Overpower' . $i, false);
                                    $this->SetValue('Overvoltage' . $i, false);
                                    $errors = '';
                                    foreach ($switch['errors'] as $key => $error) {
                                        switch ($error) {
                                            case 'overtemp':
                                                $this->SetValue('Overtemp' . $i, true);
                                                break;
                                            case 'overpower':
                                                $this->SetValue('Overpower' . $i, true);
                                                break;
                                            case 'Overvoltage':
                                                $this->SetValue('Overvoltage' . $i, true);
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
