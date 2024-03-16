<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/MQTTHelper.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';

class ShellyConfigurator extends IPSModule
{
    use MQTTHelper;
    use DebugHelper;
    //{65462305-608D-4E48-B532-E3D389F7DF00}
    private static $DeviceTypes = [
        'SHSW-1' => [
            'Name'  => 'Shelly 1',
            'GUID'  => '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}'
        ],
        'SHSW-PM' => [
            'Name'  => 'Shelly 1PM',
            'GUID'  => '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}'
        ],
        'SHSW-L' => [
            'Name'  => 'Shelly 1L',
            'GUID'  => '{9E5FA0B2-AA98-48D5-AE07-78DEA4B0370A}'
        ],
        'SHSW-21' => [
            'Name'  => 'Shelly 2',
            'GUID'  => '{BE266877-6642-4A80-9BAA-8C5B3B4DAF80}'
        ],
        'SHSW-25' => [
            'Name'  => 'Shelly 2.5',
            'GUID'  => '{BE266877-6642-4A80-9BAA-8C5B3B4DAF80}'
        ],
        'SHIX3-1' => [
            'Name'  => 'Shelly i3',
            'GUID'  => '{2B0AD1B9-1335-6C50-5CEC-DDCD03DAE252}'
        ],
        'SHEM' => [
            'Name'  => 'Shelly EM',
            'GUID'  => '{53A4EF84-0CF9-44D4-B70E-4B84E0DCE9B3}'
        ],
        'SHEM-3' => [
            'Name'  => 'Shelly 3EM',
            'GUID'  => '{108ECEFF-642A-4B1F-9608-E592E31DBA11}'
        ],
        'SHUNI-1' => [
            'Name'  => 'Shelly Uni',
            'GUID'  => '{D10AF7A0-CBC0-415A-BD3B-FFF0E8BB8B21}'
        ],
        'SHTRV-01' => [
            'Name'  => 'Shelly TRV',
            'GUID'  => '{FEBA9798-EB8E-4703-A9BC-C1B3EE711D1B}'
        ],
        'SHBTN-1' => [
            'Name'  => 'Shelly Button1',
            'GUID'  => '{B1BEE0E4-5ADE-4326-98A8-1F7B3731E456}'
        ],
        'SHBTN-2' => [
            'Name'  => 'Shelly Button1',
            'GUID'  => '{B1BEE0E4-5ADE-4326-98A8-1F7B3731E456}'
        ],
        'SHPLG-1' => [
            'Name'  => 'Shelly Plug',
            'GUID'  => '{ED5E1057-C47A-4D73-A130-B4E2912A026C}'
        ],
        'SHPLG2-1' => [
            'Name'  => 'Shelly Plug 2',
            'GUID'  => '{ED5E1057-C47A-4D73-A130-B4E2912A026C}'
        ],
        'SHPLG-S' => [
            'Name'  => 'Shelly Plug S',
            'GUID'  => '{ED5E1057-C47A-4D73-A130-B4E2912A026C}'
        ],
        'SHRGBW2' => [
            'Name'  => 'Shelly RGBW 2',
            'GUID'  => '{3286C438-2174-E03B-85CE-B6B7C1A685D0}'
        ],
        'SHDM-1' => [
            'Name'  => 'Shelly Dimmer',
            'GUID'  => '{69B501C7-DCE8-4A4A-910C-D3954473E654}'
        ],
        'SHDM-2' => [
            'Name'  => 'Shelly Dimmer 2',
            'GUID'  => '{69B501C7-DCE8-4A4A-910C-D3954473E654}'
        ],
        'SHBDUO-1' => [
            'Name'  => 'Shelly Duo',
            'GUID'  => '{6FEE58E6-082D-6934-F49E-EC6642E39992}'
        ],
        'SHSPOT-1' => [
            'Name'  => 'Shelly Duo GU10',
            'GUID'  => '{6FEE58E6-082D-6934-F49E-EC6642E39992}'
        ],
        'SHVIN-1' => [
            'Name'  => 'Shelly Vintage',
            'GUID'  => '{9BFE4A38-47C9-775E-A6BE-DA338817A639}'
        ],
        'SHBLB-1' => [
            'Name'  => 'Shelly Bulb',
            'GUID'  => '{65462305-608D-4E48-B532-E3D389F7DF00}'
        ],
        'SHCB-1' => [
            'Name'  => 'Shelly Bulb RGBW',
            'GUID'  => '{65462305-608D-4E48-B532-E3D389F7DF00}'
        ],
        'SHHT-1' => [
            'Name'  => 'Shelly H&T',
            'GUID'  => '{F2EE9948-94F6-4BA6-BDC9-E59E440F3DB0}'
        ],
        'SHWT-1' => [
            'Name'  => 'Shelly Flood',
            'GUID'  => '{C360BA67-99A3-4F37-932B-B851D4E10AD6}'
        ],
        'SHDW-1' => [
            'Name'  => 'Shelly Door/Window',
            'GUID'  => '{24BDCF16-A370-6F72-8CBD-9B9968899FED}'
        ],
        'SHDW-2' => [
            'Name'  => 'Shelly Door/Window 2',
            'GUID'  => '{24BDCF16-A370-6F72-8CBD-9B9968899FED}'
        ],
        'SHGS-1' => [
            'Name'  => 'Shelly Gas',
            'GUID'  => '{8725928A-A390-42FA-B045-A182499767C1}'
        ],
        'SHMOS-01' => [
            'Name'  => 'Shelly Motion',
            'GUID'  => '{DB241FB8-F26D-4F74-82E4-66F046931B6E}'
        ],
        'SHMOS-02' => [
            'Name'  => 'Shelly Motion 2',
            'GUID'  => '{2F27E9AF-9B26-4952-A7BF-25EAFFCA75E0}'
        ],
        'SNSW-001X16EU' => [
            'Name'  => 'Shelly Plus 1',
            'GUID'  => '{AF5127F4-4929-49AF-9894-D7B8627667A7}'
        ],
        'SNSW-001P16EU' => [
            'Name'  => 'Shelly Plus 1PM',
            'GUID'  => '{AF5127F4-4929-49AF-9894-D7B8627667A7}'
        ],
        'SNSW-002P16EU' => [
            'Name'  => 'Shelly Plus 2PM',
            'GUID'  => '{6AE60C94-A295-4A0F-9AF3-C051C1D72AAA}'
        ],
        'SNSW-102P16EU' => [
            'Name'  => 'Shelly Plus 2PM',
            'GUID'  => '{6AE60C94-A295-4A0F-9AF3-C051C1D72AAA}'
        ],
        'SNSN-0024X' => [
            'Name'  => 'Shelly Plus i4',
            'GUID'  => '{34DD2E1E-47CD-47BC-938E-071AE60FE2AD}'
        ],
        'SNSN-0D24X' => [
            'Name'  => 'Shelly Plus i4 DC',
            'GUID'  => '{34DD2E1E-47CD-47BC-938E-071AE60FE2AD}'
        ],
        'SNSN-0013A' => [
            'Name'  => 'Shelly Plus H&T',
            'GUID'  => '{41C32508-A08D-40E8-870C-AF051A8DB6B4}'
        ],
        'S3SN-0U12A' => [
            'Name'  => 'Shelly H&T Gen3',
            'GUID'  => '{41C32508-A08D-40E8-870C-AF051A8DB6B4}'
        ],
        'SNPL-00110IT' => [
            'Name'  => 'Shelly Plus Plug IT',
            'GUID'  => '{D7769710-EED1-4835-AC2D-C0AC8356E900}'
        ],
        'SNPL-00112EU' => [
            'Name'  => 'Shelly Plus Plug S V1',
            'GUID'  => '{D7769710-EED1-4835-AC2D-C0AC8356E900}'
        ],
        'SNPL-10112EU' => [
            'Name'  => 'Shelly Plus Plug S V2',
            'GUID'  => '{D7769710-EED1-4835-AC2D-C0AC8356E900}'
        ],
        'SNPL-00112UK' => [
            'Name'  => 'Shelly Plus Plug UK',
            'GUID'  => '{D7769710-EED1-4835-AC2D-C0AC8356E900}'
        ],
        'SNPL-00116US' => [
            'Name'  => 'Shelly Plus Plug US',
            'GUID'  => '{D7769710-EED1-4835-AC2D-C0AC8356E900}'
        ],
        'SNSN-0031Z' => [
            'Name'  => 'Shelly Plus Smoke',
            'GUID'  => '{2B1FC768-7B87-47C6-ACCF-9A8C601CF776}'
        ],
        'SNSN-0031Z' => [
            'Name'  => 'Shelly Plus Smoke',
            'GUID'  => '{2B1FC768-7B87-47C6-ACCF-9A8C601CF776}'
        ],
        'SNDM-0013US' => [ //fehlt
            'Name'  => 'Shelly Plus Wall Dimmer',
            'GUID'  => ''
        ],
        'SNDM-00100WW' => [ //fehlt
            'Name'  => 'Shelly Plus 0-10 V Dimmer',
            'GUID'  => '{88F80513-AE05-84EF-7120-E3F0E02C7F52}'
        ],
        'SNSW-001X8EU' => [
            'Name'  => 'Shelly Plus 1 Mini',
            'GUID'  => '{AF5127F4-4929-49AF-9894-D7B8627667A7}'
        ],
        'S3SW-001X8EU' => [
            'Name'  => 'Shelly 1 Mini Gen3',
            'GUID'  => '{D6B33C50-1855-F2B2-EC6A-0C14F4259952}'
        ],
        'S3SW-001P8EU' => [
            'Name'  => 'Shelly 1PM Mini Gen3',
            'GUID'  => '{D6B33C50-1855-F2B2-EC6A-0C14F4259952}'
        ],
        'S3PM-001PCEU16' => [
            'Name'  => 'Shelly PM Mini Gen3',
            'GUID'  => '{EA5280A7-811D-D2E3-A5A1-DF6C81505CE8}'
        ],
        'SNSW-001P8EU' => [
            'Name'  => 'Shelly Plus 1PM Mini',
            'GUID'  => '{AF5127F4-4929-49AF-9894-D7B8627667A7}'
        ],
        'SNPM-001PCEU16' => [
            'Name'  => 'Shelly Plus PM Mini',
            'GUID'  => '{5E1866C8-609B-4080-AD7C-5C766DD829A2}'
        ],
        'SPSW-001XE16EU' => [
            'Name'  => 'Shelly Pro 1',
            'GUID'  => '{03E01942-F28A-4A91-93DB-EE981EA41507}'
        ],
        'SPSW-201XE16EU' => [
            'Name'  => 'Shelly Pro 1 v.1',
            'GUID'  => '{03E01942-F28A-4A91-93DB-EE981EA41507}'
        ],
        'SPSW-001PE16EU' => [
            'Name'  => 'Shelly Pro 1PM',
            'GUID'  => '{03E01942-F28A-4A91-93DB-EE981EA41507}'
        ],
        'SPSW-201PE16EU' => [
            'Name'  => 'Shelly Pro 1PM v.1',
            'GUID'  => '{03E01942-F28A-4A91-93DB-EE981EA41507}'
        ],
        'SPSW-002XE16EU' => [
            'Name'  => 'Shelly Pro 2',
            'GUID'  => '{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}'
        ],
        'SPSW-202XE16EU' => [
            'Name'  => 'Shelly Pro 2 v.1',
            'GUID'  => '{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}'
        ],
        'SPSW-002PE16EU' => [
            'Name'  => 'Shelly Pro 2 PM',
            'GUID'  => '{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}'
        ],
        'SPSW-202PE16EU' => [
            'Name'  => 'Shelly Pro 2PM v.1',
            'GUID'  => '{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}'
        ],
        'XXX-SPSH-002PE16EU' => [ //fehlt - falsches model?
            'Name'  => 'Shelly Pro Dual Cover/Shutter PM',
            'GUID'  => ''
        ],
        'SPSW-003XE16EU' => [
            'Name'  => 'Shelly Pro 3',
            'GUID'  => '{B9FF443D-5D7F-44F5-B743-59DC70B3E633}'
        ],
        'SPEM-003CEBEU' => [
            'Name'  => 'Shelly Pro 3EM',
            'GUID'  => '{ED673810-352A-4D63-B035-55DF6BDA86AB}'
        ],
        'SPEM-003CEBEU120' => [
            'Name'  => 'Shelly Pro 3EM',
            'GUID'  => '{ED673810-352A-4D63-B035-55DF6BDA86AB}'
        ],
        'SPEM-003CEBEU400' => [
            'Name'  => 'Shelly Pro 3EM-400',
            'GUID'  => '{ED673810-352A-4D63-B035-55DF6BDA86AB}'
        ],
        'SPEM-002CEBEU50' => [
            'Name'  => 'Shelly Pro 3EM-50',
            'GUID'  => '{E6CD1DA6-EFFC-4DA0-979B-9DC6B1648891}'
        ],
        'SPSW-004PE16EU' => [
            'Name'  => 'Shelly Pro 4PM V1',
            'GUID'  => '{4E416C32-833A-4469-97B3-D4A41413A272}'
        ],
        'SPSW-104PE16EU' => [
            'Name'  => 'Shelly Pro 4PM V2',
            'GUID'  => '{4E416C32-833A-4469-97B3-D4A41413A272}'
        ],
        'SNGW-BT01' => [
            'Name'  => 'Shelly Bluetooth Gateway',
            'GUID'  => '{5B4C60D3-A1AB-CA1D-323C-A0CDCEB1D990}'
        ],
        'SPDM-001PE01EU' => [
            'Name'  => 'Shelly Pro Dimmer 1PM',
            'GUID'  => '{7785B8E6-F990-0D1E-A658-8EE5D84B4754}'
        ],
        'SPDM-002PE01EU' => [
            'Name'  => 'Shelly Pro Dimmer 2PM',
            'GUID'  => '{7785B8E6-F990-0D1E-A658-8EE5D84B4754}'
        ],
        'SNSN-0043X' => [
            'Name'  => 'Shelly Plus Uni',
            'GUID'  => '{6997986C-A636-A888-EEFB-7886787DEBF8}'
        ]
    ];

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        $this->RegisterAttributeString('Shellies', '{}');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->SetReceiveDataFilter('.*shellies/announce.*');
        $this->getShellies();
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString, true);
        $this->SendDebug('JSON', $Buffer, 0);

        //Für MQTT Fix in IPS Version 6.3
        if (IPS_GetKernelDate() > 1670886000) {
            $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
        }
        $Shellies = json_decode($this->ReadAttributeString('Shellies'), true); //$this->findShellysOnNetwork();

        if (array_key_exists('Topic', $Buffer)) {
            if ($Buffer['Topic'] == 'shellies/announce') {
                $Shelly = [];

                $Payload = json_decode($Buffer['Payload'], true);

                $foundedKey = array_search($Payload['id'], array_column($Shellies, 'ID'));
                if ($foundedKey !== false) {
                    $Shellies[$foundedKey]['LastActivity'] = time();
                    $Shellies[$foundedKey]['Model'] = (array_key_exists('model', $Payload)) ? ($Payload['model']) : '';
                    $Shellies[$foundedKey]['MAC'] = $Payload['mac'];
                    if (array_key_exists('gen', $Payload)) {
                        $Shellies[$foundedKey]['Name'] = $Payload['name'];
                        $Shellies[$foundedKey]['Firmware'] = $Payload['fw_id'];
                    } else {
                        $Shellies[$foundedKey]['Firmware'] = $Payload['fw_ver'];
                        $Shellies[$foundedKey]['IP'] = $Payload['ip'];
                    }
                    $this->WriteAttributeString('Shellies', json_encode($Shellies));
                    return;
                }
                $Shelly = [];
                $Shelly['Name'] = '-';
                $Shelly['ID'] = $Payload['id'];
                $Shellies[$foundedKey]['Model'] = (array_key_exists('model', $Payload)) ? ($Payload['model']) : '';
                $Shelly['MAC'] = $Payload['mac'];
                $Shelly['IP'] = '-';
                $Shelly['Gen'] = 'gen1';
                $Shelly['LastActivity'] = time();

                if (array_key_exists('gen', $Payload)) {
                    $Shelly['Name'] = $Payload['name'];
                    $Shelly['Firmware'] = $Payload['fw_id'];
                    $Shelly['Gen'] = $Payload['gen'];
                } else {
                    $Shelly['Firmware'] = $Payload['fw_ver'];
                    $Shelly['IP'] = $Payload['ip'];
                }
                array_push($Shellies, $Shelly);
            }
            $this->WriteAttributeString('Shellies', json_encode($Shellies));
        }
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $this->getShellies();
        if (floatval(IPS_GetKernelVersion()) < 5.3) {
            return json_encode($Form);
        }

        $Shellies = json_decode($this->ReadAttributeString('Shellies'), true); //$this->findShellysOnNetwork();
        $Values = [];

        if (count($Shellies) == 0) {
            $Form['actions'][1]['visible'] = true;
        } else {
            $Form['actions'][1]['visible'] = false;
        }

        if (count($Shellies) > 0) {
            foreach ($Shellies as $key => $Shelly) {
                $DeviceType = '';
                $instanceID = $this->getShellyInstances($Shelly['ID']);
                if ($Shelly['Model'] == '') {
                    $this->LogMessage('Shelly with IP: ' . $Shelly['IP'] . ' has no model! Check firmware updates.', KL_ERROR);
                    continue;
                }

                $DeviceType = '';
                $moduleID = '';
                if (array_key_exists($Shelly['Model'], self::$DeviceTypes)) {
                    $DeviceType = self::$DeviceTypes[$Shelly['Model']]['Name'];
                    $moduleID = self::$DeviceTypes[$Shelly['Model']]['GUID'];
                } else {
                    $DeviceType = $this->Translate('Unknown') . ' (' . $Shelly['Model'] . ')';
                }
                $AddValue = [
                    'MQTTTopic'             => $Shelly['ID'],
                    'InstanceName'          => $this->getInstanceName($instanceID),
                    'DeviceType'            => $DeviceType,
                    'IPAddress'             => $Shelly['IP'],
                    'Firmware'              => $Shelly['Firmware'],
                    'instanceID'            => $instanceID
                ];
                switch ($Shelly['Model']) {
                    case 'SHSW-1':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID'],
                                'Device'    => 'shelly1'
                            ]
                        ];
                        break;
                    case 'SHSW-PM':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID'],
                                'Device'    => 'shelly1pm'
                            ]
                        ];
                        break;
                    case 'SHSW-L':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID'],
                                'Device'    => 'shelly1l'
                            ]
                        ];
                        break;
                    case 'SHSW-21':
                        $AddValue['create'] = [
                            'Shelly 2 Relay' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['ID'],
                                    'Device'     => 'shelly2',
                                    'DeviceType' => 'relay'
                                ]
                            ],
                            'Shelly 2 Shutter' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['ID'],
                                    'Device'     => 'shelly2',
                                    'DeviceType' => 'roller'
                                ]
                            ]
                        ];
                        break;
                    case 'SHSW-25':
                        $AddValue['create'] = [
                            'Shelly 2.5 Relay' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['ID'],
                                    'Device'     => 'shelly2.5',
                                    'DeviceType' => 'relay'
                                ]
                            ],
                            'Shelly 2.5 Shutter' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'  => $Shelly['ID'],
                                    'Device'     => 'shelly2.5',
                                    'DeviceType' => 'roller'
                                ]
                            ]
                        ];
                        break;
                    case 'SHIX3-1':
                    case 'SHEM':
                    case 'SHEM-3':
                    case 'SHUNI-1':
                    case 'SHTRV-01':
                    case 'SHBTN-1':
                    case 'SHPLG2-1':
                    case 'SHPLG-S':
                    case 'SHPLG-1':
                    case 'SHDM-1':
                    case 'SHDM-2':
                    case 'SHVIN-1':
                    case 'SHBLB-1':
                    case 'SHHT-1':
                    case 'SHWT-1':
                    case 'SHGS':
                    case 'SHMOS-01':
                    case 'SHMOS-02':
                    case 'SNSW-002P16EU':
                    case 'SNSW-102P16EU':
                    case 'SNSN-0024X':
                    case 'SNSN-0D24X':
                    case 'SNSN-0013A':
                    case 'S3SN-0U12A':
                    case 'SNSN-0031Z':
                    case 'SNSW-001P8EU':
                    case 'SNPM-001PCEU16':
                    case 'SPSW-002PE16EU':
                    case 'SPSW-002XE16EU':
                    case 'SPSW-202XE16EU':
                    case 'SPSW-003XE16EU':
                    case 'SPEM-003CEBEU':
                    case 'SPEM-003CEBEU120':
                    case 'SPEM-003CEBEU400':
                    case 'SPEM-002CEBEU50':
                    case 'SPSW-004PE16EU':
                    case 'SPSW-104PE16EU':
                    case 'SNDM-00100WW':
                    case 'SNGW-BT01':
                    case 'S3PM-001PCEU16':
                    case 'SNSN-0043X':
                        $AddValue['create'] = [
                            'name'          => $Shelly['ID'],
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID']
                            ]
                        ];
                        break;
                    case 'SHRGBW2':
                        $AddValue['create'] = [
                            'Shelly RGBW2 Color' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'       => $Shelly['ID'],
                                    'DeviceType'      => 'Color'
                                ]
                            ],
                            'Shelly RGBW2 White' => [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'       => $Shelly['ID'],
                                    'DeviceType'      => 'White'
                                ]
                            ]
                        ];
                        break;
                    case 'SHBDUO-1':
                    case 'SHSPOT-1':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID'],
                                'Device'    => 'light'
                            ]
                        ];
                        break;
                    case 'SHCB-1':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID'],
                                'Device'    => 'color'
                            ]
                        ];
                        break;
                    case 'SHDW-1':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic'  => $Shelly['ID'],
                                'Device'     => 'DW'
                            ]
                        ];
                        break;
                    case 'SHDW-2':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic'  => $Shelly['ID'],
                                'Device'     => 'DW2'
                            ]
                        ];
                        break;
                    case 'SNSW-001X16EU':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => strtolower($Shelly['ID']),
                                'Device'    => 'shellyplus1'
                            ]
                        ];
                        break;
                    case 'SNSW-001P16EU':
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => strtolower($Shelly['ID']),
                                'Device'    => 'shellyplus1pm'
                            ]
                        ];
                        break;
                    case 'SNPL-00110IT':
                        case 'SNPL-00112EU':
                        case 'SNPL-10112EU':
                        case 'SNPL-00112UK':
                        case 'SNPL-00116US':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'shellyplusplugs'
                                ]
                            ];
                            break;
                        case 'S3SW-001X8EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'gen3shelly1mini'
                                ]
                            ];
                            break;
                        case 'S3SW-001P8EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'gen3shelly1pmmini'
                                ]
                            ];
                            break;
                        case 'SNSW-001X8EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'shellyplus1mini'
                                ]
                            ];
                            break;
                        case 'SNSW-001P8EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'shellyplus1pmmini'
                                ]
                            ];
                            break;
                        case 'SNSW-001P8EU':
                        case 'SPSW-201XE16EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'shellypro1'
                                ]
                            ];
                            break;
                        case 'SPSW-001PE16EU':
                        case 'SPSW-201PE16EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => strtolower($Shelly['ID']),
                                    'Device'    => 'shellypro1pm'
                                ]
                            ];
                            break;
                        case 'SPDM-001PE01EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['ID'],
                                    'Device'    => 'shellyprodimmer1pm'
                                ]
                            ];
                            break;
                        case 'SPDM-002PE01EU':
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic' => $Shelly['ID'],
                                    'Device'    => 'shellyprodimmer2pm'
                                ]
                            ];
                            break;
                        case 'SPSW-202PE16EU': //Eine Version fehlt - fehlt in der Doku von Shelly?!
                            $moduleID = '{A7B9C446-E5C6-4DE9-AF1E-B9FE20FFF3FF}';
                            $DeviceType = 'Shelly Pro 2PM';
                            $AddValue['create'] = [
                                'moduleID'      => $moduleID,
                                'info'          => $Shelly['IP'],
                                'configuration' => [
                                    'MQTTTopic'  => strtolower($Shelly['ID']),
                                    'Device'     => 'shellypro2pm',
                                    'DeviceType' => 'relay'
                                ]
                            ];
                            break;
                    case 'shellysense': // model id unbekannt
                        $moduleID = '{F86F268B-BC23-41AC-B107-16EEF661A4D7}';
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID']
                            ]
                        ];
                        break;
                    case 'shellysmoke': // model id unbekannt
                        $moduleID = '{88A5611C-CD57-4255-9F57-E420CE784C81}';
                        $DeviceType = 'Shelly Smoke';
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID']
                            ]
                        ];
                        break;
                    case 'shellyair': // model id unbekannt
                        $moduleID = '{55840D9D-BB28-4D66-91B5-66C8859FAE83}';
                        $DeviceType = 'Shelly Air';
                        $AddValue['create'] = [
                            'moduleID'      => $moduleID,
                            'info'          => $Shelly['IP'],
                            'configuration' => [
                                'MQTTTopic' => $Shelly['ID']
                            ]
                        ];
                        break;
                    default:
                        $this->SendDebug(__FUNCTION__ . ' DeviceType', 'Invalid Device Type:' . $Shelly['Model'], 0);
                        break;
                    }

                $Values[] = $AddValue;
            }
            $Form['actions'][0]['values'] = $Values;
        }
        return json_encode($Form);
    }

    public function getShellies()
    {
        $Shellies = json_decode($this->ReadAttributeString('Shellies'), true);

        foreach ($Shellies as $key => $Shelly) {
            if ($Shelly['LastActivity'] + 86400 < time()) {
                unset($Shellies[$key]);
                $Shellies = array_values($Shellies);
            }

            $this->WriteAttributeString('Shellies', json_encode($Shellies));
        }
        $this->sendMQTT('shellies/command', 'announce');
    }

    private function getShellyInstances($ShellyID)
    {
        $InstanceIDs = [];
        foreach (self::$DeviceTypes as $key => $value) {
            if ($value['GUID'] != '') {
                $InstanceIDs[] = IPS_GetInstanceListByModuleID($value['GUID']);
            }
        }

        foreach ($InstanceIDs as $IDs) {
            foreach ($IDs as $id) {
                if (strtolower(IPS_GetProperty($id, 'MQTTTopic')) == strtolower($ShellyID)) {
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
