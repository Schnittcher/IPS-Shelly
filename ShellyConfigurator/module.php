<?php

declare(strict_types=1);

class ShellyConfigurator extends IPSModule
{
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

        $this->SetReceiveDataFilter('this-will-never-match');
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        if (floatval(IPS_GetKernelVersion()) < 5.3) {
            return json_encode($Form);
        }

        $Shellys = $this->findShellysOnNetwork();
        $Values = [];

        if (count($Shellys) > 0) {
            foreach ($Shellys as $key => $Shelly) {
                $DeviceType = '';
                $instanceID = $this->getShellyInstances($Shelly['Name']);
                $AddValue = [
                    'name'                  => $Shelly['Name'],
                    'DeviceType'            => $Shelly['DeviceType'],
                    'IPAddress'             => $Shelly['IPv4'],
                    'Firmware'              => $Shelly['Firmware'],
                    'instanceID'            => $instanceID
                ];

                $moduleID = '';
                switch ($Shelly['DeviceType']) {
                    case 'shelly1':
                        $moduleID = '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}';
                        $DeviceType = 'Shelly 1';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                    'Device'    => 'shelly1'
                                ]
                            ]
                        ];
                        break;
                    case 'shelly1pm':
                        $moduleID = '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}';
                        $DeviceType = 'Shelly 1 PM';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                    'Device'    => 'shelly1pm'
                                ]
                            ]
                        ];
                        break;
                    case 'shellyswitch':
                        $moduleID = '{BE266877-6642-4A80-9BAA-8C5B3B4DAF80}';
                        $DeviceType = 'Shelly 2';
                        $AddValue['create'] = [
                            'Shelly 2 Relay' => [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2',
                                    'DeviceType' => 'relay'
                                ]
                            ],
                            'Shelly 2 Shutter' => [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2',
                                    'DeviceType' => 'roller'
                                ]
                            ]
                        ];
                        break;
                    case 'shellyswitch25':
                        $this->SendDebug('Name', $Shelly['Name'], 0);
                        $moduleID = '{BE266877-6642-4A80-9BAA-8C5B3B4DAF80}';
                        $DeviceType = 'Shelly 2.5';
                        $AddValue['create'] = [
                            'Shelly 2.5 Relay' => [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2.5',
                                    'DeviceType' => 'relay'
                                ]
                            ],
                            'Shelly 2.5 Shutter' => [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2.5',
                                    'DeviceType' => 'roller'
                                ]
                            ]
                        ];
                        break;
                    case 'shelly4pro':
                        $moduleID = '{F56CC544-581D-42EB-AAF0-F5E9E908916C}';
                        $DeviceType = 'Shelly 4 Pro';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                    case 'shellydimmer':
                        $moduleID = '{69B501C7-DCE8-4A4A-910C-D3954473E654}';
                        $DeviceType = 'Shelly Dimmer';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                    case 'shellyht':
                        $moduleID = '{F2EE9948-94F6-4BA6-BDC9-E59E440F3DB0}';
                        $DeviceType = 'Shelly H&T';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    case 'shellyplug':
                        $moduleID = '{ED5E1057-C47A-4D73-A130-B4E2912A026C}';
                        $DeviceType = 'Shelly Plug';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    case 'shellyem':
                        $moduleID = '{53A4EF84-0CF9-44D4-B70E-4B84E0DCE9B3}';
                        $DeviceType = 'Shelly EM';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    case 'shelly3em':
                        $moduleID = '{108ECEFF-642A-4B1F-9608-E592E31DBA11}';
                        $DeviceType = 'Shelly 3EM';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    case 'shellyrgbw2':
                        $moduleID = '{3286C438-2174-E03B-85CE-B6B7C1A685D0}';
                        $DeviceType = 'Shelly RGBW2';
                        $AddValue['create'] = [
                            'Shelly RGBW2 Color' => [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                    'Mode'      => 'Color'
                                ]
                            ],
                            'Shelly RGBW2 White' => [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                    'Mode'      => 'White'
                                ]
                            ]
                        ];
                        break;
                    case 'shellysense':
                        $moduleID = '{F86F268B-BC23-41AC-B107-16EEF661A4D7}';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    case 'shellysmoke':
                        $moduleID = '{88A5611C-CD57-4255-9F57-E420CE784C81}';
                        $DeviceType = 'Shelly Smoke';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    default:
                        $this->SendDebug(__FUNCTION__ . ' DeviceType Switch', 'Invalid Device Type', 0);
                        $DeviceType = 'Invalid';
                        break;
                    }

                $Values[] = $AddValue;
            }
            $Form['actions'][0]['values'] = $Values;
        }
        return json_encode($Form);
    }

    private function getShellyInstances($ShellyID)
    {
        $InstanceIDs = [];
        //Shelly1 Instances
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}');

        //Shelly 2
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{BE266877-6642-4A80-9BAA-8C5B3B4DAF80}');

        //Shelly 4Pro
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{F56CC544-581D-42EB-AAF0-F5E9E908916C}');

        //ShellyDimmer
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{69B501C7-DCE8-4A4A-910C-D3954473E654}');

        //ShellyEM
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{53A4EF84-0CF9-44D4-B70E-4B84E0DCE9B3}');

        //Shelly3EM
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{108ECEFF-642A-4B1F-9608-E592E31DBA11}');

        //ShellyFlood
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{C360BA67-99A3-4F37-932B-B851D4E10AD6}');

        //ShellyHT
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{F2EE9948-94F6-4BA6-BDC9-E59E440F3DB0}');

        //ShellyPlug
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{ED5E1057-C47A-4D73-A130-B4E2912A026C}');

        //ShellyRGBW2
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{3286C438-2174-E03B-85CE-B6B7C1A685D0}');

        //ShellySense
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{F86F268B-BC23-41AC-B107-16EEF661A4D7}');

        //ShellySmoke
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{88A5611C-CD57-4255-9F57-E420CE784C81}');

        //ShellyWindow
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{24BDCF16-A370-6F72-8CBD-9B9968899FED}');

        foreach ($InstanceIDs as $IDs) {
            foreach ($IDs as $id) {
                if (IPS_GetProperty($id, 'MQTTTopic') == $ShellyID) {
                    return $id;
                }
            }
        }
        return 0;
    }

    private function findShellysOnNetwork()
    {
        $mDNSInstanceIDs = IPS_GetInstanceListByModuleID('{780B2D48-916C-4D59-AD35-5A429B2355A5}');
        $resultServiceTypes = ZC_QueryServiceType($mDNSInstanceIDs[0], '_http._tcp', '');

        $shellys = [];
        foreach ($resultServiceTypes as $key => $device) {
            if (strpos($device['Name'], 'shelly') !== false) {
                $shelly = [];

                $deviceInfo = ZC_QueryService($mDNSInstanceIDs[0], $device['Name'], '_http._tcp', 'local.');

                $type = strstr($device['Name'], '-', true);
                $shelly['Name'] = $device['Name'];
                $shelly['IPv4'] = $deviceInfo[0]['IPv4'][0];
                if ($type != 'shellysense') {
                    $shelly['DeviceType'] = strstr($device['Name'], '-', true);
                    if (array_key_exists(1, $deviceInfo[0]['TXTRecords'])) {
                        $shelly['Firmware'] = $deviceInfo[0]['TXTRecords'][1];
                    }
                    $shelly['Firmware'] = '-';
                } else {
                    $shelly['DeviceType'] = '-';
                    $shelly['Firmware'] = '-';
                }
                $shellys[] = $shelly;
            }
        }
        return $shellys;
    }
}
