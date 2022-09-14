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
                switch (strtolower($Shelly['DeviceType'])) {
                    case 'shelly1':
                        $moduleID = '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}';
                        $DeviceType = 'Shelly 1';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                    'Device'    => 'shelly1pm'
                                ]
                            ]
                        ];
                        break;
                    case 'shelly1l':
                        $moduleID = '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}';
                        $DeviceType = 'Shelly 1L';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                    'Device'    => 'shelly1l'
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2',
                                    'DeviceType' => 'relay'
                                ]
                            ],
                            'Shelly 2 Shutter' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2',
                                    'DeviceType' => 'roller'
                                ]
                            ]
                        ];
                        break;
                    case 'shellyswitch25':
                        $moduleID = '{BE266877-6642-4A80-9BAA-8C5B3B4DAF80}';
                        $DeviceType = 'Shelly 2.5';
                        $AddValue['create'] = [
                            'Shelly 2.5 Relay' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['Name'],
                                    'Device'     => 'shelly2.5',
                                    'DeviceType' => 'relay'
                                ]
                            ],
                            'Shelly 2.5 Shutter' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name'],
                                ]
                            ]
                        ];
                        break;
                    case 'shellydimmer':
                    case 'shellydimmer2':
                        $moduleID = '{69B501C7-DCE8-4A4A-910C-D3954473E654}';
                        $DeviceType = 'Shelly Dimmer';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                    case 'shellyem3':
                        $moduleID = '{108ECEFF-642A-4B1F-9608-E592E31DBA11}';
                        $DeviceType = 'Shelly 3EM';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic'       => $Shelly['Name'],
                                    'DeviceType'      => 'Color'
                                ]
                            ],
                            'Shelly RGBW2 White' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic'       => $Shelly['Name'],
                                    'DeviceType'      => 'White'
                                ]
                            ]
                        ];
                        break;
                    case 'shellysense':
                        $moduleID = '{F86F268B-BC23-41AC-B107-16EEF661A4D7}';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
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
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                    case 'shellyflood':
                        $moduleID = '{C360BA67-99A3-4F37-932B-B851D4E10AD6}';
                        $DeviceType = 'Shelly Flood';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                    case 'shellyvintage':
                        $moduleID = '{9BFE4A38-47C9-775E-A6BE-DA338817A639}';
                        $DeviceType = 'Shelly Vintage';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                    case 'shellyair':
                        $moduleID = '{55840D9D-BB28-4D66-91B5-66C8859FAE83}';
                        $DeviceType = 'Shelly Air';
                        $AddValue['create'] = [
                            [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IPv4'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['Name']
                                ]
                            ]
                        ];
                        break;
                        case 'shellybutton1':
                            $moduleID = '{B1BEE0E4-5ADE-4326-98A8-1F7B3731E456}';
                            $DeviceType = 'Shelly Button1';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                        case 'shellydw':
                            $moduleID = '{24BDCF16-A370-6F72-8CBD-9B9968899FED}';
                            $DeviceType = 'Shelly Door / Window';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic'  => $Shelly['Name'],
                                        'Device'     => 'DW'
                                    ]
                                ]
                            ];
                            break;
                        case 'shellydw2':
                            $moduleID = '{24BDCF16-A370-6F72-8CBD-9B9968899FED}';
                            $DeviceType = 'Shelly Door / Window';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic'  => $Shelly['Name'],
                                        'Device'     => 'DW2'
                                    ]
                                ]
                            ];
                            break;
                        case 'shellygas':
                            $moduleID = '{8725928A-A390-42FA-B045-A182499767C1}';
                            $DeviceType = 'Shelly Gas';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                        case 'shellyix3':
                            $moduleID = '{2B0AD1B9-1335-6C50-5CEC-DDCD03DAE252}';
                            $DeviceType = 'Shelly i3';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                        case 'shellybulbduo':
                            $moduleID = '{6FEE58E6-082D-6934-F49E-EC6642E39992}';
                            $DeviceType = 'Shelly Duo';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name'],
                                        'Device'    => 'light'
                                    ]
                                ]
                            ];
                            break;
                        case 'shellyuni':
                            $moduleID = '{D10AF7A0-CBC0-415A-BD3B-FFF0E8BB8B21}';
                            $DeviceType = 'Shelly Uni';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                        case 'shellycolorbulb':
                            $moduleID = '{65462305-608D-4E48-B532-E3D389F7DF00}';
                            $DeviceType = 'Shelly Bulb RGBW';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                        case 'shellymotionsensor':
                            $moduleID = '{DB241FB8-F26D-4F74-82E4-66F046931B6E}';
                            $DeviceType = 'Shelly Motion';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                        case 'shellypro4pm':
                            $moduleID = '{4E416C32-833A-4469-97B3-D4A41413A272}';
                            $DeviceType = 'Shelly Pro 4PM';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name'])
                                    ]
                                ]
                            ];
                            break;
                        case 'shellypro1':
                            $moduleID = '{03E01942-F28A-4A91-93DB-EE981EA41507}';
                            $DeviceType = 'Shelly Pro 1';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name']),
                                        'Device'    => 'shellypro1'
                                    ]
                                ]
                            ];
                            break;
                        case 'shellypro1pm':
                            $moduleID = '{03E01942-F28A-4A91-93DB-EE981EA41507}';
                            $DeviceType = 'Shelly Pro 1PM';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name']),
                                        'Device'    => 'shellypro1pm'
                                    ]
                                ]
                            ];
                            break;
                            case 'shellypro2':
                                $moduleID = '{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}';
                                $DeviceType = 'Shelly Pro 2';
                                $AddValue['create'] = [
                                    [
                                        'moduleID'      => $moduleID,
                                        'info'          => $Shelly['IPv4'],
                                        'configuration' => [
                                            'MQTTTopic' => strtolower($Shelly['Name']),
                                        ]
                                    ]
                                ];
                                break;
                        case 'shellyplusi4':
                            $moduleID = '{34DD2E1E-47CD-47BC-938E-071AE60FE2AD}';
                            $DeviceType = 'Shelly Plus i4';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name'])
                                    ]
                                ]
                            ];
                            break;
                        case 'shellyplusht':
                            $moduleID = '{41C32508-A08D-40E8-870C-AF051A8DB6B4}';
                            $DeviceType = 'Shelly Plus H&T';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name']),
                                    ]
                                ]
                            ];
                            break;
                        case 'shellyplus1':
                            $moduleID = '{AF5127F4-4929-49AF-9894-D7B8627667A7}';
                            $DeviceType = 'Shelly Plus 1';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name']),
                                        'Device'    => 'shellyplus1'
                                    ]
                                ]
                            ];
                            break;
                        case 'shellyplus1pm':
                            $moduleID = '{AF5127F4-4929-49AF-9894-D7B8627667A7}';
                            $DeviceType = 'Shelly Plus 1PM';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name']),
                                        'Device'    => 'shellyplus1pm'
                                    ]
                                ]
                            ];
                            break;
                        case 'shellyplus2pm':
                            $moduleID = '{6AE60C94-A295-4A0F-9AF3-C051C1D72AAA}';
                            $DeviceType = 'Shelly Plus 2PM';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => strtolower($Shelly['Name'])
                                    ]
                                ]
                            ];
                            break;
                        case 'shellytrv':
                            $moduleID = '{FEBA9798-EB8E-4703-A9BC-C1B3EE711D1B}';
                            $DeviceType = 'Shelly TRV';
                            $AddValue['create'] = [
                                [
                                    'moduleID'      => $moduleID,
                                    'info'          => $Shelly['IPv4'],
                                    'configuration' => [
                                        'MQTTTopic' => $Shelly['Name']
                                    ]
                                ]
                            ];
                            break;
                    default:
                        $this->SendDebug(__FUNCTION__ . ' DeviceType Switch', 'Invalid Device Type:' . strtolower($Shelly['DeviceType']), 0);
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

        //ShellyDimmer2
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

        //ShellyVintage
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{9BFE4A38-47C9-775E-A6BE-DA338817A639}');

        //ShellyAir
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{55840D9D-BB28-4D66-91B5-66C8859FAE83}');

        //ShellyButton1
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{B1BEE0E4-5ADE-4326-98A8-1F7B3731E456}');

        //ShellyGas
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{8725928A-A390-42FA-B045-A182499767C1}');

        //Shelly i3
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{2B0AD1B9-1335-6C50-5CEC-DDCD03DAE252}');

        //Shelly Duo
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{6FEE58E6-082D-6934-F49E-EC6642E39992}');

        //Shelly Duo
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{65462305-608D-4E48-B532-E3D389F7DF00}');

        //Shelly Uni
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{D10AF7A0-CBC0-415A-BD3B-FFF0E8BB8B21}');

        //ShellyMotion
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{DB241FB8-F26D-4F74-82E4-66F046931B6E}');

        //Shelly Plus H&T
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{41C32508-A08D-40E8-870C-AF051A8DB6B4}');

        //Shelly Plus 1 PM
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{34DD2E1E-47CD-47BC-938E-071AE60FE2AD}');

        //Shelly Plus 1 PM
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{AF5127F4-4929-49AF-9894-D7B8627667A7}');

        //Shelly Plus 2 PM
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{6AE60C94-A295-4A0F-9AF3-C051C1D72AAA}');

        //Shelly Pro 4PM
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{4E416C32-833A-4469-97B3-D4A41413A272}');

        //Shelly Pro 1
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{03E01942-F28A-4A91-93DB-EE981EA41507}');
        //Shelly Pro 2
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}');

        //Shelly TRV
        $InstanceIDs[] = IPS_GetInstanceListByModuleID('{FEBA9798-EB8E-4703-A9BC-C1B3EE711D1B}');

        foreach ($InstanceIDs as $IDs) {
            foreach ($IDs as $id) {
                if (strtolower(IPS_GetProperty($id, 'MQTTTopic')) == strtolower($ShellyID)) {
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

        $this->SendDebug('resultServiceTypes', print_r($resultServiceTypes, true), 0);

        $shellys = [];
        foreach ($resultServiceTypes as $key => $device) {
            if (strpos(strtolower($device['Name']), 'shelly') !== false) {
                $shelly = [];

                $deviceInfo = ZC_QueryService($mDNSInstanceIDs[0], $device['Name'], '_http._tcp', 'local.');

                $type = strstr($device['Name'], '-', true);
                $shelly['Name'] = $device['Name'];
                if (array_key_exists(0, $deviceInfo)) {
                    if (array_key_exists(0, $deviceInfo[0]['IPv4'])) {
                        //$this->LogMessage(print_r($deviceInfo, true), KL_NOTIFY);
                        $shelly['IPv4'] = $deviceInfo[0]['IPv4'][0];
                    } else {
                        $shelly['IPv4'] = '-';
                    }
                } else {
                    $shelly['IPv4'] = '-';
                }
                if ($type != 'shellysense') {
                    $shelly['DeviceType'] = strstr($device['Name'], '-', true);
                    $shelly['Firmware'] = '-';
                    $this->SendDebug('mDNS TXTRecords', print_r($deviceInfo, true), 0);
                    if (array_key_exists(0, $deviceInfo)) {
                        if (is_array($deviceInfo[0])) {
                            if (array_key_exists(1, $deviceInfo[0]['TXTRecords'])) {
                                $shelly['Firmware'] = $deviceInfo[0]['TXTRecords'][1];
                            } else {
                                $shelly['Firmware'] = '-';
                            }
                        } else {
                            $shelly['Firmware'] = '-';
                        }
                    } else {
                        $shelly['Firmware'] = '-';
                    }
                }
                $shellys[] = $shelly;
            }
        }
        return $shellys;
    }
}
