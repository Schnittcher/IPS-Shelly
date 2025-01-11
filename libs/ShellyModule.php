<?php

declare(strict_types=1);

require_once __DIR__ . '/ShellyHelper.php';
require_once __DIR__ . '/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/MQTTHelper.php';

class ShellyModule extends IPSModule
{
    use Shelly;
    use DebugHelper;
    use VariableProfileHelper;
    use MQTTHelper;

    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('Device', '');

        $this->RegisterProfileBooleanEx('Shelly.Reachable', 'Network', '', '', [
            [false, 'Offline',  '', 0xFF0000],
            [true, 'Online',  '', 0x00FF00]
        ]);
        $this->RegisterProfileFloat('Shelly.Input.Percent', '', '', ' %', 0, 100, 0.01, 2);

        $Variables = [];
        foreach (static::$Variables as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Devices'      => $Variable[4],
                'DeviceType'   => $Variable[5],
                'Action'       => $Variable[6],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[7],
                'Zeroing'      => $Variable[8]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
    }

    public function Migrate($JSONData)
    {

        // Diese Zeile nicht entfernen
        parent::Migrate($JSONData);

        // Eigenschaften/Attribute migrieren
        $j = json_decode($JSONData, true);
        $Variables = json_decode($j['configuration']['Variables'], true);

        //Zeroing kam später dazu, deswegen die Migrate Funktion
        foreach ($Variables as $key => &$value) {
            if (!array_key_exists('Zeroing', $value)) {
                $value['Zeroing'] = false;
            }
        }
        $j['configuration']['Variables'] = json_encode($Variables);
        return json_encode($j, JSON_FORCE_OBJECT);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        $NewRows = static::$Variables;
        $NewPos = 0;
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);
        foreach ($Variables as $Variable) {
            $Devices = $Variable['Devices'];
            if ($Variable['Devices'] == null) {
                $Devices = [];
            }

            $Device = '';
            $Device = @$this->ReadPropertyString('Device');

            $DeviceType = '';
            $DeviceType = @$this->ReadPropertyString('DeviceType');

            $VariableActive = $Variable['Keep'] && (in_array($Device, $Devices) || empty($Devices)) && (($DeviceType == $Variable['DeviceType']) || $Variable['DeviceType'] == '');

            @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $VariableActive);
            if ($Variable['Action'] && $VariableActive) {
                $this->EnableAction($Variable['Ident']);
            }
            foreach ($NewRows as $Index => $Row) {
                if ($Variable['Ident'] == str_replace(' ', '', $Row[0])) {
                    unset($NewRows[$Index]);
                }
            }
            if ($NewPos < $Variable['Pos']) {
                $NewPos = $Variable['Pos'];
            }
        }

        if (count($NewRows) != 0) {
            foreach ($NewRows as $NewVariable) {
                $Variables[] = [
                    'Ident'        => str_replace(' ', '', $NewVariable[0]),
                    'Name'         => $this->Translate($NewVariable[1]),
                    'VarType'      => $NewVariable[2],
                    'Profile'      => $NewVariable[3],
                    'Devices'      => $NewVariable[4],
                    'DeviceType'   => $NewVariable[5],
                    'Action'       => $NewVariable[6],
                    'Pos'          => ++$NewPos,
                    'Keep'         => $NewVariable[7],
                    'Zeroing'      => $NewVariable[8]
                ];
            }
            IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
            IPS_ApplyChanges($this->InstanceID);
            return;
        }
    }

    public function resetVariables()
    {
        $NewRows = static::$Variables;
        $Variables = [];
        foreach ($NewRows as $Pos => $Variable) {
            $Variables[] = [
                'Ident'        => str_replace(' ', '', $Variable[0]),
                'Name'         => $this->Translate($Variable[1]),
                'VarType'      => $Variable[2],
                'Profile'      => $Variable[3],
                'Devices'      => $Variable[4],
                'DeviceType'   => $Variable[5],
                'Action'       => $Variable[6],
                'Pos'          => $Pos + 1,
                'Keep'         => $Variable[7],
                'Zeroing'      => $Variable[8]
            ];
        }
        IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
        IPS_ApplyChanges($this->InstanceID);
        return;
    }

    protected function SetValue($Ident, $Value)
    {
        if (@$this->GetIDForIdent($Ident)) {
            $this->SendDebug('SetValue :: ' . $Ident, $Value, 0);
            parent::SetValue($Ident, $Value);
        }
    }

    protected function zeroingValues()
    {
        $Variables = json_decode($this->ReadPropertyString('Variables'), true);

        foreach ($Variables as $key => $variable) {
            if ($variable['Zeroing']) {
                switch ($variable['VarType']) {
                    case VARIABLETYPE_BOOLEAN:
                        $this->SetValue($variable['Ident'], false);
                        break;
                    case VARIABLETYPE_STRING:
                        $this->SetValue($variable['Ident'], '');
                        break;
                    case VARIABLETYPE_FLOAT:
                    case VARIABLETYPE_INTEGER:
                        $this->SetValue($variable['Ident'], 0);
                        break;
                    default:
                        $this->LogMessage('Error by zeroing Values.', KL_ERROR);
                        break;
                }
            }
        }
    }
}