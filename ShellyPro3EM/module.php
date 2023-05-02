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

        ['bCurrent', 'Phase B Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true],
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

        ['aTotalActEnergy', 'Phase A total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['aTotalActRetEnergy', 'Phase A total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['bTotalActEnergy', 'Phase B total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['bTotalActRetEnergy', 'Phase B total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['cTotalActEnergy', 'Phase C total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['cTotalActRetEnergy', 'Phase C total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['totalActEnergy', 'Total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['totalActRetEnergy', 'Total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['consumptionNetted', 'Consumption netted', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['energyFromGridNetted', 'Energy from Grid netted', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],
        ['energyToGridNetted', 'Energy to Grid netted', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true],

        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyFloat('TotalActiveEnergyOffset', 0);
        $this->RegisterPropertyFloat('TotalActRetEnergyOffset', 0);
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
                        if (array_key_exists('emdata:0', $Payload['params'])) {
                            $consumptionNetted = 0;
                            $emData = $Payload['params']['emdata:0'];
                            $this->SetValue('aTotalActEnergy', floatval($emData['a_total_act_energy']) / 1000);
                            $this->SetValue('aTotalActRetEnergy', floatval($emData['a_total_act_ret_energy']) / 1000);
                            $this->SetValue('bTotalActEnergy', floatval($emData['b_total_act_energy']) / 1000);
                            $this->SetValue('bTotalActRetEnergy', floatval($emData['b_total_act_ret_energy']) / 1000);
                            $this->SetValue('cTotalActEnergy', floatval($emData['c_total_act_energy']) / 1000);
                            $this->SetValue('cTotalActRetEnergy', floatval($emData['c_total_act_ret_energy']) / 1000);

                            $this->SetValue('totalActEnergy', (floatval($emData['total_act']) / 1000) + $this->ReadPropertyFloat('TotalActiveEnergyOffset'));
                            $this->SetValue('totalActRetEnergy', (floatval($emData['total_act_ret']) / 1000) + $this->ReadPropertyFloat('TotalActRetEnergyOffset'));

                            $consumptionNetted = ((floatval($emData['a_total_act_energy']) / 1000) + (floatval($emData['b_total_act_energy']) / 1000) + (floatval($emData['c_total_act_energy']) / 1000)) - ((floatval($emData['a_total_act_ret_energy']) / 1000) + (floatval($emData['b_total_act_ret_energy']) / 1000) + (floatval($emData['c_total_act_ret_energy']) / 1000));
                            $this->SetValue('consumptionNetted', $consumptionNetted);

                            if ($consumptionNetted > $this->GetValue('energyToGridNetted')) {
                                $this->SetValue('energyFromGridNetted', $consumptionNetted);
                            } else {
                                $this->SetValue('energyToGridNetted', abs($consumptionNetted));
                                
                            }
                        }
                    }
                }
            }
        }
    }
}
