<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_ShellyRGBW2 extends IPSModule
{
    use Shelly,
        ShellyRGBW2Action;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        $this->RegisterPropertyString('MQTTTopic', '');
        $this->RegisterPropertyString('Mode', 'Color');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        switch ($this->ReadPropertyString('Mode')) {
            case 'Color':
                $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State'), '~Switch');
                $this->EnableAction('Shelly_State');

                $this->RegisterVariableInteger('Shelly_Color', $this->Translate('Color'), '~HexColor');
                $this->EnableAction('Shelly_Color');

                $this->RegisterVariableInteger('Shelly_White', $this->Translate('White'), 'Intensity.255');
                $this->EnableAction('Shelly_White');

                $this->RegisterVariableInteger('Shelly_Gain', $this->Translate('Gain'), 'Intensity.100');
                $this->EnableAction('Shelly_Gain');

                //TODO Effect
                $this->RegisterProfileAssociation(
                    'Shelly.Effect',
                    'Bulb',
                    '',
                    '',
                    0,
                    6,
                    0,
                    0,
                    1,
                    [
                        [0, $this->Translate('Off'), 'Bulb', -1],
                        [1, $this->Translate('Meteor Shower'), 'Bulb', -1],
                        [2, $this->Translate('Gradual Change'), 'Bulb', -1],
                        [3, $this->Translate('Breath'), 'Bulb', -1],
                        [4, $this->Translate('Flash'), 'Bulb', -1],
                        [5, $this->Translate('On/Off Gradual'), 'Bulb', -1],
                        [6, $this->Translate('Red/Green Change'), 'Bulb', -1]
                    ]
                );
                $this->RegisterVariableInteger('Shelly_Effect', $this->Translate('Effect'), 'Shelly.Effect');
                $this->EnableAction('Shelly_Effect');

                $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower', $this->Translate('Overpower'), '');

                break;
            case 'White':
                $this->RegisterVariableBoolean('Shelly_State', $this->Translate('State 1'), '~Switch');
                $this->RegisterVariableBoolean('Shelly_State2', $this->Translate('State 2'), '~Switch');
                $this->RegisterVariableBoolean('Shelly_State3', $this->Translate('State 3'), '~Switch');
                $this->RegisterVariableBoolean('Shelly_State4', $this->Translate('State 4'), '~Switch');

                $this->EnableAction('Shelly_State');
                $this->EnableAction('Shelly_State2');
                $this->EnableAction('Shelly_State3');
                $this->EnableAction('Shelly_State4');

                $this->RegisterVariableInteger('Shelly_Brightness', $this->Translate('Brightness 1'), 'Intensity.100');
                $this->RegisterVariableInteger('Shelly_Brightness2', $this->Translate('Brightness 2'), 'Intensity.100');
                $this->RegisterVariableInteger('Shelly_Brightness3', $this->Translate('Brightness 3'), 'Intensity.100');
                $this->RegisterVariableInteger('Shelly_Brightness4', $this->Translate('Brightness 4'), 'Intensity.100');

                $this->EnableAction('Shelly_Brightness');
                $this->EnableAction('Shelly_Brightness2');
                $this->EnableAction('Shelly_Brightness3');
                $this->EnableAction('Shelly_Brightness4');

                $this->RegisterVariableFloat('Shelly_Power', $this->Translate('Power 1'), '');
                $this->RegisterVariableFloat('Shelly_Power2', $this->Translate('Power 2'), '');
                $this->RegisterVariableFloat('Shelly_Power3', $this->Translate('Power 3'), '');
                $this->RegisterVariableFloat('Shelly_Power4', $this->Translate('Power 4'), '');

                $this->RegisterVariableBoolean('Shelly_Overpower', $this->Translate('Overpower 1'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower2', $this->Translate('Overpower 2'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower3', $this->Translate('Overpower 3'), '');
                $this->RegisterVariableBoolean('Shelly_Overpower4', $this->Translate('Overpower 4'), '');
                break;
        }
        //Setze Filter für ReceiveData
        $MQTTTopic = $this->ReadPropertyString('MQTTTopic');
        $this->SetReceiveDataFilter('.*' . $MQTTTopic . '.*');
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('ShlleyRGBW2 Mode', $this->ReadPropertyString('Mode'), 0);
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString);
            // Buffer decodieren und in eine Variable schreiben
            $this->SendDebug('MQTT Topic', $Buffer->Topic, 0);
            if (property_exists($Buffer, 'Topic')) {
                $channel = $this->getChannelRelay($Buffer->Topic);
                //Ist es ein ShellyRGBW2? Wenn ja weiter machen!
                if (fnmatch('*shellyrgbw2*', $Buffer->Topic)) {
                    $this->SendDebug('ShellyRGBW2 Topic', $Buffer->Topic, 0);
                    $this->SendDebug('ShellyRGBW2 Payload', $Buffer->Payload, 0);
                    $Payload = json_decode($Buffer->Payload);
                    if (fnmatch('*status*', $Buffer->Topic)) {
                        switch ($Payload->mode) {
                            case 'white':
                                if (strtolower($this->ReadPropertyString('Mode')) != $Payload->mode) {
                                    $this->SendDebug('Mode', strtolower($this->ReadPropertyString('Mode')) . ' ' . $Payload->mode, 0);
                                    break;
                                }
                                switch ($channel) {
                                    case 0:
                                        SetValue($this->GetIDForIdent('Shelly_State'), $Payload->ison);
                                        SetValue($this->GetIDForIdent('Shelly_Brightness'), $Payload->brightness);
                                        SetValue($this->GetIDForIdent('Shelly_Power'), $Payload->power);
                                        SetValue($this->GetIDForIdent('Shelly_Overpower'), $Payload->overpower);
                                        break;
                                    case 1:
                                        SetValue($this->GetIDForIdent('Shelly_State2'), $Payload->ison);
                                        SetValue($this->GetIDForIdent('Shelly_Brightness2'), $Payload->brightness);
                                        SetValue($this->GetIDForIdent('Shelly_Power2'), $Payload->power);
                                        SetValue($this->GetIDForIdent('Shelly_Overpower2'), $Payload->overpower);
                                        break;
                                    case 2:
                                        SetValue($this->GetIDForIdent('Shelly_State3'), $Payload->ison);
                                        SetValue($this->GetIDForIdent('Shelly_Brightness3'), $Payload->brightness);
                                        SetValue($this->GetIDForIdent('Shelly_Power3'), $Payload->power);
                                        SetValue($this->GetIDForIdent('Shelly_Overpower3'), $Payload->overpower);
                                        break;
                                    case 3:
                                        SetValue($this->GetIDForIdent('Shelly_State4'), $Payload->ison);
                                        SetValue($this->GetIDForIdent('Shelly_Brightness4'), $Payload->brightness);
                                        SetValue($this->GetIDForIdent('Shelly_Power4'), $Payload->power);
                                        SetValue($this->GetIDForIdent('Shelly_Overpower4'), $Payload->overpower);
                                        break;
                                    default:
                                        break;
                                }
                                break;
                            case 'color':
                                if (strtolower($this->ReadPropertyString('Mode')) != $Payload->mode) {
                                    $this->SendDebug('Mode', strtolower($this->ReadPropertyString('Mode')) . ' ' . $Payload->mode, 0);
                                    break;
                                }
                                // {"ison":true,"mode":"color","red":255,"green":251,"blue":241,"white":0,"gain":100,"effect":0,"power":5.90,"overpower":false}
                                SetValue($this->GetIDForIdent('Shelly_State'), $Payload->ison);
                                SetValue($this->GetIDForIdent('Shelly_Color'), $this->rgbToHex($Payload->red, $Payload->green, $Payload->blue));
                                SetValue($this->GetIDForIdent('Shelly_White'), $Payload->white);
                                SetValue($this->GetIDForIdent('Shelly_Gain'), $Payload->gain);
                                SetValue($this->GetIDForIdent('Shelly_Effect'), $Payload->effect);
                                SetValue($this->GetIDForIdent('Shelly_Power'), $Payload->power);
                                SetValue($this->GetIDForIdent('Shelly_Overpower'), $Payload->overpower);
                                break;
                            default:
                                $this->SendDebug('Invalid Mode', $Payload->mode, 0);
                                break;
                        }
                    }
                }
            }
        }
    }
}
