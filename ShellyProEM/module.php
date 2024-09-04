<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyProEM extends ShellyModule
{
    public static $Variables = [
        ['State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],

        ['aCurrent', 'Phase A Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['aVoltage', 'Phase A Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['aActPower', 'Phase A active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['aAprtPower', 'Phase A apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['aPF', 'Phase A Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true, false],

        ['bCurrent', 'Phase B Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['bVoltage', 'Phase B Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['bActPower', 'Phase B active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['bAprtPower', 'Phase B apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['bPF', 'Phase B Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true, false],

        ['aTotalActEnergy', 'Phase A total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['aTotalActRetEnergy', 'Phase A total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['bTotalActEnergy', 'Phase B total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['bTotalActRetEnergy', 'Phase B total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],

        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function Create()
    {
        parent::Create();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
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

                if (fnmatch('*/status/switch:0', $Buffer['Topic'])) {
                    if (array_key_exists('output', $Payload)) {
                        $this->SetValue('State', $Payload['output']);
                    }
                }

                if (fnmatch('*/status/em1:*', $Buffer['Topic'])) {
                    if (array_key_exists('id', $Payload)) {
                        if ($Payload['id'] == 0) {
                            $this->SetValue('aCurrent', $Payload['current']);
                            $this->SetValue('aVoltage', $Payload['voltage']);
                            $this->SetValue('aActPower', $Payload['act_power']);
                            $this->SetValue('aAprtPower', $Payload['aprt_power']);
                            $this->SetValue('aPF', $Payload['pf']);
                        }
                        if ($Payload['id'] == 1) {
                            $this->SetValue('bCurrent', $Payload['current']);
                            $this->SetValue('bVoltage', $Payload['voltage']);
                            $this->SetValue('bActPower', $Payload['act_power']);
                            $this->SetValue('bAprtPower', $Payload['aprt_power']);
                            $this->SetValue('bPF', $Payload['pf']);
                        }
					}
				}

                if (fnmatch('*/status/em1data:*', $Buffer['Topic'])) {
                    if (array_key_exists('id', $Payload)) {
                        if ($Payload['id'] == 0) {
                            $this->SetValue('aTotalActEnergy', $Payload['total_act_energy']);
                            $this->SetValue('aTotalActRetEnergy', $Payload['total_act_ret_energy']);
                        }
                        if ($Payload['id'] == 1) {
                            $this->SetValue('bTotalActEnergy', $Payload['total_act_energy']);
                            $this->SetValue('bTotalActRetEnergy', $Payload['total_act_ret_energy']);
                        }
                    }
                }
            }
        }
    }

    public function ToggleAfter(int $switch, bool $value, int $toggle_after)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Switch.Set';
        $Payload['params'] = ['id' => $switch, 'on' => $value, 'toggle_after' => $toggle_after];

        $this->sendMQTT($Topic, json_encode($Payload));
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
