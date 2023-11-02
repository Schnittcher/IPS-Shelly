<?php

declare(strict_types=1);

require_once __DIR__ . '/ShellyHelper.php';
require_once __DIR__ . '/vendor/SymconModulHelper/VariableProfileHelper.php';
require_once __DIR__ . '/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/MQTTHelper.php';

class ShellyModuleBLU extends IPSModule
{
    use Shelly;
    use DebugHelper;
    use VariableProfileHelper;
    use MQTTHelper;

    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('Event', 'shelly-blu');
        $this->RegisterPropertyString('BLUAddress', '');

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
                'Keep'         => $Variable[7]
            ];
        }
        $this->RegisterPropertyString('Variables', json_encode($Variables));
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        //Setze Filter für ReceiveData
        $BLUAddress = $this->ReadPropertyString('BLUAddress');
        $this->SetReceiveDataFilter('.*' . $BLUAddress . '.*');

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
                    'Keep'         => $NewVariable[7]
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
                'Keep'         => $Variable[7]
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
}