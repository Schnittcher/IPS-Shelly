<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';
require_once __DIR__ . '/../libs/helper/VariableProfileHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyGas extends IPSModule
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

        $this->RegisterProfileIntegerEx('Shelly.Gas.Operation', 'TurnLeft', '', '', [
            [0, $this->Translate('Unknown'),  '', 0xFFA500],
            [1, $this->Translate('Warmup'),  '', 0xDFDF1c],
            [2, $this->Translate('Normal'),  '', 0x00FF00],
            [3, $this->Translate('Fault'),  '', 0xFF0000],
        ]);

        $this->RegisterProfileIntegerEx('Shelly.Gas', 'Gas', '', '', [
            [0, $this->Translate('Unknown'),  '', 0xFFA500],
            [1, $this->Translate('No Alarm'),  '', 0x00FF00],
            [2, $this->Translate('Mild'),  '', 0xFFA500],
            [3, $this->Translate('Heavy'),  '', 0xFF0000],
            [4, $this->Translate('Test'),  '', 0xDFDF1c],
        ]);

        $this->RegisterProfileIntegerEx('Shelly.Gas.SelfTest', 'Shuffle', '', '', [
            [0, $this->Translate('Not Completed'),  '', 0xFF0000],
            [1, $this->Translate('Completed'),  '', 0x00FF00],
            [2, $this->Translate('Running'),  '', 0x00FF00],
            [3, $this->Translate('Pending'),  '', 0x00FF00],
        ]);

        $this->RegisterProfileIntegerEx('Shelly.Gas.Control', 'Remote', '', '', [
            [0, $this->Translate('Start Self Test'),  '', -1],
            [1, $this->Translate('Mute'),  '', -1],
            [2, $this->Translate('Unmute'),  '', -1],
        ]);

        $this->RegisterVariableInteger('Shelly_Operation', $this->Translate('Operation'), 'Shelly.Gas.Operation');
        $this->RegisterVariableInteger('Shelly_Gas', $this->Translate('Gas'), 'Shelly.Gas');
        $this->RegisterVariableInteger('Shelly_SelfTest', $this->Translate('Self Test'), 'Shelly.Gas.SelfTest');
        $this->RegisterVariableInteger('Shelly_Concentration', $this->Translate('Concentration'));
        $this->RegisterVariableInteger('Shelly_Control', $this->Translate('Control'), 'Shelly.Gas.Control');
        $this->RegisterVariableBoolean('Shelly_Reachable', $this->Translate('Reachable'), 'Shelly.Reachable');

        $this->EnableAction('Shelly_Control');
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

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_Control':
                switch ($Value) {
                    case 0:
                        $this->sendMQTT('shellies/shellygas-<deviceid>/sensor/start_self_test', '');
                        break;
                    case 1:
                        $this->sendMQTT('shellies/shellygas-<deviceid>/sensor/mute', '');
                        break;
                    case 2:
                        $this->sendMQTT('shellies/shellygas-<deviceid>/sensor/unmute', '');
                        break;
                    default:
                        $this->LogMessage($this->Translate('Invalid Value for Control: ') . $Value, KL_ERROR);
                        break;
                }
                break;
            default:
                $this->LogMessage($this->Translate('Invalid Ident for RequestAction: ') . $Ident, KL_ERROR);
                break;
            }
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
                if (fnmatch('*/sensor/operation*', $Buffer->Topic)) {
                    $this->SendDebug('Operation Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'unknown':
                            $this->SetValue('Shelly_Operation', 0);
                            break;
                        case 'warmup':
                            $this->SetValue('Shelly_Operation', 1);
                            break;
                        case 'normal':
                            $this->SetValue('Shelly_Operation', 2);
                            break;
                        case 'fault':
                            $this->SetValue('Shelly_Operation', 3);
                            break;
                        default:
                        $this->LogMessage($this->Translate('Invalid Value for Operation: ') . $Buffer->Payload, KL_ERROR);
                            break;
                    }
                }
                if (fnmatch('*/sensor/gas*', $Buffer->Topic)) {
                    $this->SendDebug('Gas Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'unknown':
                            $this->SetValue('Shelly_Gas', 0);
                            break;
                        case 'none':
                            $this->SetValue('Shelly_Gas', 1);
                            break;
                        case 'mild':
                            $this->SetValue('Shelly_Gas', 2);
                            break;
                        case 'heavy':
                            $this->SetValue('Shelly_Gas', 3);
                            break;
                        case 'test':
                            $this->SetValue('Shelly_Gas', 4);
                            break;
                        default:
                            $this->LogMessage($this->Translate('Invalid Value for Gas: ') . $Buffer->Payload, KL_ERROR);
                            break;
                    }
                }
                if (fnmatch('*/sensor/self_test*', $Buffer->Topic)) {
                    $this->SendDebug('Self Test Payload', $Buffer->Payload, 0);
                    switch ($Buffer->Payload) {
                        case 'not_completed':
                            $this->SetValue('Shelly_SelfTest', 0);
                            break;
                        case 'completed':
                            $this->SetValue('Shelly_SelfTest', 1);
                            break;
                        case 'running':
                            $this->SetValue('Shelly_SelfTest', 2);
                            break;
                        case 'pending':
                            $this->SetValue('Shelly_SelfTest', 3);
                            break;
                        case 'test':
                            $this->SetValue('Shelly_SelfTest', 4);
                            break;
                        default:
                            $this->LogMessage($this->Translate('Invalid Value for Self Test: ') . $Buffer->Payload, KL_ERROR);
                            break;
                    }
                }

                if (fnmatch('*/sensor/concentration*', $Buffer->Topic)) {
                    $this->SendDebug('Concentration Payload', $Buffer->Payload, 0);
                    $this->SetValue('Shelly_Concentration', $Buffer->Payload);
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

    private function SwitchMode(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}
