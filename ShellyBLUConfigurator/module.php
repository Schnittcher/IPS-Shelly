<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';

class ShellyBLUConfigurator extends IPSModule
{
    use DebugHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $Filter = '"event":"shelly-blu"';
        $Filter = 'shelly-blu';
        $this->SetReceiveDataFilter('.*' . $Filter . '.*');

        $this->RegisterAttributeString('Devices', '{}');
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString, true);
        $this->SendDebug('JSON', $Buffer, 0);

        $Devices = json_decode($this->ReadAttributeString('Devices'), true);

        //Für MQTT Fix in IPS Version 6.3
        if (IPS_GetKernelDate() > 1670886000) {
            $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
        }
        if (array_key_exists('Topic', $Buffer)) {
            if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                $Payload = json_decode($Buffer['Payload'], true);
                if (array_key_exists('params', $Payload)) {
                    if (array_key_exists('events', $Payload['params'])) {
                        if (array_key_exists('data', $Payload['params']['events'][0])) {
                            if (array_key_exists('data', $Payload['params']['events'][0])) {
                                $data = $Payload['params']['events'][0]['data'];

                                $DeviceType = '';
                                if (array_key_exists('button', $data)) {
                                    $devcieType = 'Shelly BLU Button 1';
                                }
                                if (array_key_exists('window', $data)) {
                                    $devcieType = 'Shelly BLU Door/Window';
                                }
                                if (array_key_exists('motion', $data)) {
                                    $devcieType = 'Shelly BLU Motion';
                                }
                                if (array_key_exists('humidity', $data)) {
                                    $devcieType = 'Shelly BLU H&T';
                                }

                                if (!array_key_exists($data['address'], $Devices)) {
                                    $Devices[$data['address']] = $devcieType;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->WriteAttributeString('Devices', json_encode($Devices));
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        if (floatval(IPS_GetKernelVersion()) < 5.3) {
            return json_encode($Form);
        }

        $Values = [];
        $Devices = json_decode($this->ReadAttributeString('Devices'), true);
        if (count($Devices) > 0) {
            foreach ($Devices as $BLUAddress => $Device) {
                $instanceID = $this->getShellyInstances($BLUAddress);
                $AddValue = [
                    'name'                   => $Device,
                    'InstanceName'           => $this->getInstanceName($instanceID),
                    'BLUAddress'             => $BLUAddress,
                    'instanceID'             => $instanceID
                ];

                $moduleID = '';
                switch ($Device) {
                    case 'Shelly BLU Button 1':
                        $moduleID = '{5E02DB53-B7BD-4479-AC5C-09E7519BD89F}';
                        $DeviceType = $Device;
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $BLUAddress,
                            'configuration' => [
                                'BLUAddress' => $BLUAddress,
                                'Event'      => 'shelly-blu'
                            ]
                        ];
                        break;
                    case 'Shelly BLU Door/Window':
                        $moduleID = '{3551089F-4CDF-4440-B7FA-3ACB88CAD23F}';
                        $DeviceType = $Device;
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $BLUAddress,
                            'configuration' => [
                                'BLUAddress' => $BLUAddress,
                                'Event'      => 'shelly-blu'
                            ]
                        ];
                        break;
                    case 'Shelly BLU Motion':
                        $moduleID = '{2F6CA178-2817-4F78-A88B-1783997CEC0E}';
                        $DeviceType = $Device;
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $BLUAddress,
                            'configuration' => [
                                'BLUAddress' => $BLUAddress,
                                'Event'      => 'shelly-blu'
                            ]
                        ];
                        break;
                    case 'Shelly BLU H&T':
                        $moduleID = '{C077278B-316D-7027-CA62-5D4EBDCE1769}';
                        $DeviceType = $Device;
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $BLUAddress,
                            'configuration' => [
                                'BLUAddress' => $BLUAddress,
                                'Event'      => 'shelly-blu'
                            ]
                        ];
                        break;
                    default:
                        $this->SendDebug(__FUNCTION__ . ' DeviceType Switch', 'Invalid Device Type:' . $Device, 0);
                        $DeviceType = 'Invalid';
                        break;
                    }

                $Values[] = $AddValue;
            }
            $Form['actions'][0]['values'] = $Values;
        }
        return json_encode($Form);
    }

    private function getShellyInstances($BLUAddress)
    {
        $InstanceIDs = [];
        //Shelly BLU Button 1
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{5E02DB53-B7BD-4479-AC5C-09E7519BD89F}');

        //Shelly BLU Door/Window
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{3551089F-4CDF-4440-B7FA-3ACB88CAD23F}');

        //Shelly BLU Motion
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{2F6CA178-2817-4F78-A88B-1783997CEC0E}');

        foreach ($InstanceIDs as $IDs) {
            foreach ($IDs as $id) {
                if (strtolower(IPS_GetProperty($id, 'BLUAddress')) == $BLUAddress) {
                    if (IPS_GetInstance($id)['ConnectionID'] === IPS_GetInstance($this->InstanceID)['ConnectionID']) {
                        return $id;
                    }
                }
            }
        }
        return 0;
    }

    private function getInstanceName($ID)
    {
        if ($ID != 0) {
            return IPS_GetObject($ID)['ObjectName'];
        }
        return '';
    }
}
