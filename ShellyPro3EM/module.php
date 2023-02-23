<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPro3EM extends ShellyModule
{
    public static $Variables = [
        ['aCurrent', 'Phase A Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['aVoltage', 'Phase A Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['aActPower', 'Phase A active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['aAprtPower', 'Phase A apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['aPF', 'Phase A Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true],

        ['bCurrent', 'Phase V Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['bVoltage', 'Phase B Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['bActPower', 'Phase B active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['bAprtPower', 'Phase B apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['bPF', 'Phase B Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true],

        ['cCurrent', 'Phase C Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['cVoltage', 'Phase C Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['cActPower', 'Phase C active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['cAprtPower', 'Phase C apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['cPF', 'Phase C Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true],

        ['totalCurrent', 'Total Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
        ['totalActPower', 'Total active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],
        ['totalAprtPower', 'Total apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true],

        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString, true);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
            }

            $this->SendDebug('MQTT Topic', $Buffer['Topic'], 0);

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('em:0', $Payload['params'])) {
                            $em = $Payload['params']['em:0'];
                            $this->SetValue('aCurrent', $em['a_current']);
                            $this->SetValue('aVoltage', $em['a_voltage']);
                            $this->SetValue('aActPower', $em['a_act_power']);
                            $this->SetValue('aAprtPower', $em['a_aprt_power']);
                            $this->SetValue('aPF', $em['a_pf']);

                            $this->SetValue('bCurrent', $em['b_current']);
                            $this->SetValue('bVoltage', $em['b_voltage']);
                            $this->SetValue('bActPower', $em['b_act_power']);
                            $this->SetValue('bAprtPower', $em['b_aprt_power']);
                            $this->SetValue('bPF', $em['b_pf']);

                            $this->SetValue('cCurrent', $em['c_current']);
                            $this->SetValue('cVoltage', $em['c_voltage']);
                            $this->SetValue('cActPower', $em['c_act_power']);
                            $this->SetValue('cAprtPower', $em['c_aprt_power']);
                            $this->SetValue('cPF', $em['c_pf']);

                            $this->SetValue('totalCurrent', $em['total_current']);
                            $this->SetValue('totalActPower', $em['total_act_power']);
                            $this->SetValue('totalAprtPower', $em['total_aprt_power']);
                        }
                    }
                }
            }
        }
    }
}