<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_Shelly2 extends IPSModule
{
    use ShellyRelayAction,
        ShellyRollerAction;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{EE0D345A-CF31-428A-A613-33CE98E752DD}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('DeviceType', '');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{EE0D345A-CF31-428A-A613-33CE98E752DD}');
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');

        switch ($this->ReadPropertyString('DeviceType')) {
            case 'relay':
                $this->SendDebug(__FUNCTION__ . ' Device Type: ', ' Relay', 0);
                $this->RegisterVariableBoolean('Shelly_State', 'State', '~Switch');
                $this->EnableAction('Shelly_State');
                $this->RegisterVariableBoolean('Shelly_State1', 'State 2', '~Switch');
                $this->EnableAction('Shelly_State1');
                break;
            case 'roller':
                $this->SendDebug(__FUNCTION__ . ' Device Type: ', ' Roller', 0);
                $this->RegisterVariableInteger('Shelly_Roller', 'Roller', '~ShutterMoveStop');
                $this->EnableAction('Shelly_Roller');
                break;
            default:
                $this->SendDebug(__FUNCTION__ . ' Device Type: ', 'No Device Type', 0);

        }
        $this->RegisterVariableFloat('Shelly_Power', 'Power', '');
        $this->RegisterVariableFloat('Shelly_Energy', 'Energy', '');
    }

    public function ReceiveData($JSONString)
    {
        // Relay
        //shellies/shellyswitch-<deviceid>/relay/<i>
        //shellies/shellyswitch-<deviceid>/relay/power
        //shellies/shellyswitch-<deviceid>/relay/energy

        //Roller
        //shellies/shellyswitch-<deviceid>/roller/0

        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $Buffer = json_decode($data->Buffer);
            $this->SendDebug('MQTT Topic', $Buffer->TOPIC, 0);

            //Power Variable prüfen
            if (property_exists($Buffer, 'TOPIC')) {
                //Ist es ein Relay?
                if (fnmatch('*/relay/[01]', $Buffer->TOPIC)) {
                    $this->SendDebug('State Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('State Msg', $Buffer->MSG, 0);
                    $ShellyTopic = explode('/', $Buffer->TOPIC);
                    $LastKey = count($ShellyTopic) - 1;
                    $relay = $ShellyTopic[$LastKey];
                    $this->SendDebug(__FUNCTION__ . ' Relay', $relay, 0);

                    //Power prüfen und in IPS setzen
                    switch ($Buffer->MSG) {
                        case 'off':
                            switch ($relay) {
                                case 0:
                                    SetValue($this->GetIDForIdent('Shelly_State'), 0);
                                    break;
                                case 1:
                                    SetValue($this->GetIDForIdent('Shelly_State1'), 0);
                                    break;
                                default:
                                    break;
                            }
                            break;
                        case 'on':
                            switch ($relay) {
                                case 0:
                                    SetValue($this->GetIDForIdent('Shelly_State'), 1);
                                    break;
                                case 1:
                                    SetValue($this->GetIDForIdent('Shelly_State1'), 1);
                                    break;
                                default:
                                    break;
                            }
                            break;
                    }
                }
                if (fnmatch('*roller*', $Buffer->TOPIC)) {
                    //TODO ROLLER
                    $this->SendDebug('Roller Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Roller Msg', $Buffer->MSG, 0);
                    switch ($Buffer->MSG) {
                        case 'open':
                            SetValue($this->GetIDForIdent('Shelly_Roller'), 0);
                            break;
                        case 'stop':
                            SetValue($this->GetIDForIdent('Shelly_Roller'), 2);
                            break;
                        case 'close':
                            SetValue($this->GetIDForIdent('Shelly_Roller'), 4);
                            break;
                        default:
                            $this->SendDebug(__FUNCTION__ . ' Roller', 'Invalid Value: ' . $Buffer->MSG, 0);
                            break;
                    }
                }
                if (fnmatch('*/relay/power*', $Buffer->TOPIC)) {
                    $this->SendDebug('Power Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Power Msg', $Buffer->MSG, 0);
                    SetValue($this->GetIDForIdent('Shelly_Power'), $Buffer->MSG);
                }
                if (fnmatch('*/relay/energy*', $Buffer->TOPIC)) {
                    $this->SendDebug('Energy Topic', $Buffer->TOPIC, 0);
                    $this->SendDebug('Energy Msg', $Buffer->MSG, 0);
                    SetValue($this->GetIDForIdent('Shelly_Energy'), $Buffer->MSG);
                }
            }
        }
    }

    /**
     * public function RequestAction($Ident, $Value)
     * {
     * $this->SendDebug(__FUNCTION__ . ' Ident', $Ident, 0);
     * $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
     * $result = $this->SwitchMode($Value);
     * }.
     **/
    private function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[count($Associations) - 1][0];
        }
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }

    private function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, 1);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != 1) {
                throw new Exception('Variable profile type does not match for profile ' . $Name, E_USER_NOTICE);
            }
        }
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
    }
}
