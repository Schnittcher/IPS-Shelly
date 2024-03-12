<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPro3EM extends ShellyModule
{
    public static $Variables = [
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

        ['cCurrent', 'Phase C Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['cVoltage', 'Phase C Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['cActPower', 'Phase C active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['cAprtPower', 'Phase C apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['cPF', 'Phase C Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true, false],

        ['totalCurrent', 'Total Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['totalActPower', 'Total active Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        ['totalAprtPower', 'Total apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],

        ['aTotalActEnergy', 'Phase A total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['aTotalActRetEnergy', 'Phase A total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['bTotalActEnergy', 'Phase B total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['bTotalActRetEnergy', 'Phase B total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['cTotalActEnergy', 'Phase C total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['cTotalActRetEnergy', 'Phase C total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['totalActEnergy', 'Total active Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['totalActRetEnergy', 'Total active returned Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyFloat('TotalActiveEnergyOffset', 0);
        $this->RegisterPropertyFloat('TotalActRetEnergyOffset', 0);
        $this->RegisterPropertyBoolean('Netting', false);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->MaintainVariable('CurrentReturned', $this->Translate('Current Returned'), 2, '~Watt', 0, $this->ReadPropertyBoolean('Netting'));
        $this->MaintainVariable('CurrentImport', $this->Translate('Current Import'), 2, '~Watt', 0, $this->ReadPropertyBoolean('Netting'));
        $this->MaintainVariable('Import', $this->Translate('Import'), 2, '~Electricity', 0, $this->ReadPropertyBoolean('Netting'));
        $this->MaintainVariable('Returned', $this->Translate('Returned'), 2, '~Electricity', 0, $this->ReadPropertyBoolean('Netting'));
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

                            if ($this->ReadPropertyBoolean('Netting')) {
                                $this->Netting();
                            }
                        }
                        if (array_key_exists('emdata:0', $Payload['params'])) {
                            $emData = $Payload['params']['emdata:0'];
                            $this->SetValue('aTotalActEnergy', floatval($emData['a_total_act_energy']) / 1000);
                            $this->SetValue('aTotalActRetEnergy', floatval($emData['a_total_act_ret_energy']) / 1000);
                            $this->SetValue('bTotalActEnergy', floatval($emData['b_total_act_energy']) / 1000);
                            $this->SetValue('bTotalActRetEnergy', floatval($emData['b_total_act_ret_energy']) / 1000);
                            $this->SetValue('cTotalActEnergy', floatval($emData['c_total_act_energy']) / 1000);
                            $this->SetValue('cTotalActRetEnergy', floatval($emData['c_total_act_ret_energy']) / 1000);

                            $this->SetValue('totalActEnergy', (floatval($emData['total_act']) / 1000) + $this->ReadPropertyFloat('TotalActiveEnergyOffset'));
                            $this->SetValue('totalActRetEnergy', (floatval($emData['total_act_ret']) / 1000) + $this->ReadPropertyFloat('TotalActRetEnergyOffset'));
                        }
                    }
                }
            }
        }
    }

    private function Netting()
    {
        $Leistung = $this->GetValue('totalActPower');

        $varZwischenSpericherEinspeisung = IPS_GetVariable($this->GetIDForIdent('CurrentReturned'));
        $varZwischenSpericherBezug = IPS_GetVariable($this->GetIDForIdent('CurrentImport'));

        $ZwischenSpericherEinspeisung = $this->GetValue('CurrentReturned');
        $ZwischenSpericherBezug = $this->GetValue('CurrentImport');

        if ($ZwischenSpericherEinspeisung > 0) {
            $zeit = ($varZwischenSpericherEinspeisung['VariableChanged'] - time()) / 3600;
            $kwh = (abs($ZwischenSpericherEinspeisung) * abs($zeit)) / 1000;
            SetValue($this->GetIDForIdent('Returned'), GetValue($this->GetIDForIdent('Returned')) + $kwh);
        }

        if ($ZwischenSpericherBezug > 0) {
            $zeit = ($varZwischenSpericherBezug['VariableChanged'] - time()) / 3600;
            $kwh = ($ZwischenSpericherBezug * abs($zeit)) / 1000;
            SetValue($this->GetIDForIdent('Import'), GetValue($this->GetIDForIdent('Import')) + $kwh);
        }

        if ($Leistung < 0) {
            SetValue($this->GetIDForIdent('CurrentReturned'), abs($Leistung));
            SetValue($this->GetIDForIdent('CurrentImport'), 0);
        }
        if ($Leistung > 0) {
            SetValue($this->GetIDForIdent('CurrentImport'), abs($Leistung));
            SetValue($this->GetIDForIdent('CurrentReturned'), 0);
        }
    }
}