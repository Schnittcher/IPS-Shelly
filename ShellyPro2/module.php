<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPro2 extends ShellyModule
{
    public static $Variables = [
        ['State0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', ['shellypro2', 'shellypro2pm'], 'relay', true, true, false],
        ['Power0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', ['shellypro2pm'], 'relay', false, true, false],
        ['TotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', ['shellypro2pm'], 'relay', false, true, false],
        ['Current0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', ['shellypro2pm'], 'relay', false, true, false],
        ['Voltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', ['shellypro2pm'], 'relay', false, true, false],
        ['Powerfactor0', 'Powerfactor 1', VARIABLETYPE_FLOAT, '', ['shellypro2pm'], 'relay', false, true, false],
        ['Overtemp0', 'Overtemp 1', VARIABLETYPE_BOOLEAN, '~Alert', [], 'relay', false, true, false],
        ['Overpower0', 'Overpower 1', VARIABLETYPE_BOOLEAN, '~Alert', [], 'relay', false, true, false],
        ['Overvoltage0', 'Overvoltage 1', VARIABLETYPE_BOOLEAN, '~Alert', [], 'relay', false, true, false],

        ['State1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', ['shellypro2', 'shellypro2pm'], 'relay', true, true, false],
        ['Power1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', ['shellypro2pm'], 'relay', false, true, false],
        ['TotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', ['shellypro2pm'], 'relay', false, true, false],
        ['Current1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', ['shellypro2pm'], 'relay', false, true, false],
        ['Voltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', ['shellypro2pm'], 'relay', false, true, false],
        ['Powerfactor1', 'Powerfactor 2', VARIABLETYPE_FLOAT, '', ['shellypro2pm'], 'relay', false, true, false],
        ['Overtemp1', 'Overtemp 2', VARIABLETYPE_BOOLEAN, '~Alert', ['shellypro2', 'shellypro2pm'], 'relay', false, true, false],
        ['Overpower1', 'Overpower 2', VARIABLETYPE_BOOLEAN, '~Alert', ['shellypro2', 'shellypro2pm'], 'relay', false, true, false],
        ['Overvoltage1', 'Overvoltage 2', VARIABLETYPE_BOOLEAN, '~Alert', ['shellypro2', 'shellypro2pm'], 'relay', false, true, false],

        ['CoverState', 'State', VARIABLETYPE_STRING, 'Shelly2ProPM.CoverState', ['shellypro2', 'shellypro2pm'], 'cover', true, true, false],
        ['CoverRunningState', 'Running State', VARIABLETYPE_STRING, 'Shelly2ProPM.CoverRunningState', ['shellypro2', 'shellypro2pm'], 'cover', true, true, false],
        ['CurrentPos', 'Current Position', VARIABLETYPE_INTEGER, '~Shutter', ['shellypro2pm'], 'cover', false, true, false],
        ['TargetPos', 'Target Position', VARIABLETYPE_INTEGER, '~Shutter', ['shellypro2pm'], 'cover', true, true, false],
        ['Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', ['shellypro2pm'], 'cover', false, true, false],
        ['TotalEnergy', 'Total Energy', VARIABLETYPE_FLOAT, '~Electricity', ['shellypro2pm'], 'cover', false, true, false],
        ['Current', 'Current', VARIABLETYPE_FLOAT, '~Ampere', ['shellypro2pm'], 'cover', false, true, false],
        ['Voltage', 'Voltage', VARIABLETYPE_FLOAT, '~Volt', ['shellypro2pm'], 'cover', false, true, false],
        ['Powerfactor', 'Powerfactor', VARIABLETYPE_FLOAT, '', ['shellypro2pm'], 'cover', false, true, false],
        ['Overtemp', 'Overtemp', VARIABLETYPE_BOOLEAN, '~Alert', [], 'cover', false, true, false],
        ['Overpower', 'Overpower', VARIABLETYPE_BOOLEAN, '~Alert', [], 'cover', false, true, false],
        ['Overvoltage', 'Overvoltage', VARIABLETYPE_BOOLEAN, '~Alert', [], 'cover', false, true, false],
        ['DeviceTemperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', [], '', false, true, false],

        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyString('DeviceType', '');
        $this->RegisterProfileStringEx('Shelly2ProPM.CoverRunningState', 'Jalousie', '', '', [
            ['open', $this->Translate('Opened'), 'Jalousie', 0xFF0000],
            ['opening', $this->Translate('Opening'), 'Jalousie', 0xFF0000],
            ['closed', $this->Translate('Closed'), 'Jalousie', 0x00FF00],
            ['closing', $this->Translate('Closing'), 'Jalousie', 0x00FF00],
            ['stopped', $this->Translate('Stopped'), 'Jalousie', 0xFF8800],
            ['calibrating ', $this->Translate('Calibrating '), 'Jalousie', 0x8800FF]
        ]);

        $this->RegisterProfileStringEx('Shelly2ProPM.CoverState', 'Jalousie', '', '', [
            ['open', $this->Translate('Open'), 'Jalousie', 0xFF0000],
            ['close', $this->Translate('Close'), 'Jalousie', 0x00FF00],
            ['stop', $this->Translate('Stop'), 'Jalousie', 0xFF8800]
        ]);
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
            case 'CoverState':
                $this->CoverMode(0, $Value);
                $this->SetValue('CoverState', $Value);
                break;
            case 'TargetPos':
                $this->CoverGoToPosition(0, $Value);
                $this->SetValue('TargetPos', $Value);
                break;
            }
    }
    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString, true);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
            }

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                    if (!$Payload) {
                        $this->zeroingValues();
                    }
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        //Events
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'][0];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
                        }
                        //Switch
                        for ($i = 0; $i <= 1; $i++) {
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
                                if (array_key_exists('pf', $switch)) {
                                    $this->SetValue('Powerfactor' . $i, floatval($switch['pf']));
                                }
                                if (array_key_exists('current', $switch)) {
                                    $this->SetValue('Current' . $i, $switch['current']);
                                }
                                if (array_key_exists('aenergy', $switch)) {
                                    if (array_key_exists('total', $switch['aenergy'])) {
                                        $this->SetValue('TotalEnergy' . $i, $switch['aenergy']['total'] / 1000);
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
                            //Cover
                            if (array_key_exists('cover:0', $Payload['params'])) {
                                $cover = $Payload['params']['cover:0'];
                                if (array_key_exists('state', $cover)) {
                                    $this->SetValue('CoverRunningState', $cover['state']);
                                }
                                if (array_key_exists('apower', $cover)) {
                                    $this->SetValue('Power', $cover['apower']);
                                }
                                if (array_key_exists('voltage', $cover)) {
                                    $this->SetValue('Voltage', $cover['voltage']);
                                }
                                if (array_key_exists('pf', $cover)) {
                                    $this->SetValue('Powerfactor', floatval($cover['pf']));
                                }
                                if (array_key_exists('current', $cover)) {
                                    $this->SetValue('Current', $cover['current']);
                                }
                                if (array_key_exists('aenergy', $cover)) {
                                    if (array_key_exists('total', $cover['aenergy'])) {
                                        $this->SetValue('TotalEnergy', $cover['aenergy']['total'] / 1000);
                                    }
                                }
                                if (array_key_exists('current_pos', $cover)) {
                                    $this->SetValue('CurrentPos', $cover['current_pos']);
                                }
                                if (array_key_exists('target_pos', $cover)) {
                                    $this->SetValue('TargetPos', $cover['target_pos']);
                                }
                                if (array_key_exists('errors', $cover)) {
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
                //Temperatur ist immer vorhanden und soltle immer der selbe Wert sein.
                if (fnmatch('*/status/*', $Buffer['Topic'])) {
                    if (array_key_exists('temperature', $Payload)) {
                        if (array_key_exists('tC', $Payload['temperature'])) {
                            $this->SetValue('DeviceTemperature', $Payload['temperature']['tC']);
                        }
                    }
                }
                if (fnmatch('*/status/switch:*', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State' . $Payload['id'], $Payload['output']);
                    }
                    if (array_key_exists('apower', $Payload)) {
                        $this->SetValue('Power' . $Payload['id'], $Payload['apower']);
                    }
                    if (array_key_exists('voltage', $Payload)) {
                        $this->SetValue('Voltage' . $Payload['id'], $Payload['voltage']);
                    }
                    if (array_key_exists('pf', $Payload)) {
                        $this->SetValue('Powerfactor' . $Payload['id'], floatval($Payload['pf']));
                    }
                    if (array_key_exists('current', $Payload)) {
                        $this->SetValue('Current' . $Payload['id'], $Payload['current']);
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy' . $Payload['id'], $Payload['aenergy']['total'] / 1000);
                        }
                    }
                }
                if (fnmatch('*/status/cover:0', $Buffer['Topic'])) {
                    if (array_key_exists('state', $Payload)) {
                        $this->SetValue('CoverRunningState', $Payload['state']);
                    }
                    if (array_key_exists('apower', $Payload)) {
                        $this->SetValue('Power', $Payload['apower']);
                    }
                    if (array_key_exists('voltage', $Payload)) {
                        $this->SetValue('Voltage', $Payload['voltage']);
                    }
                    if (array_key_exists('pf', $Payload)) {
                        $this->SetValue('Powerfactor', floatval($Payload['pf']));
                    }
                    if (array_key_exists('current', $Payload)) {
                        $this->SetValue('Current', $Payload['current']);
                    }
                    if (array_key_exists('aenergy', $Payload)) {
                        if (array_key_exists('total', $Payload['aenergy'])) {
                            $this->SetValue('TotalEnergy', $Payload['aenergy']['total'] / 1000);
                        }
                    }
                }
            }
        }
    }

    private function SwitchMode(int $switchID, bool $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $switchID, 'on' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverMode(int $coverID, string $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.' . $value;
        $Payload['params'] = ['id' => $coverID];

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    private function CoverGoToPosition(int $coverID, int $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Cover.GoToPosition';
        $Payload['params'] = ['id' => $coverID, 'pos' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}
