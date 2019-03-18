<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyHelper.php';

class IPS_ShellyRGBW2 extends IPSModule
{
    use ShellyRGBW2Action;

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
            //Power Variable prüfen
            if (property_exists($Buffer, 'Topic')) {
                //Ist es ein ShellyRGBW2? Wenn ja weiter machen!
                $ShellyTopic = explode('/', $Buffer->Topic);
                $LastKey = count($ShellyTopic) - 1;
                $channel = $ShellyTopic[$LastKey];
                if (fnmatch('*shellyrgbw2*', $Buffer->Topic)) {
                    $this->SendDebug('ShellyRGBW2 Topic', $Buffer->Topic, 0);
                    $this->SendDebug('ShellyRGBW2 Payload', $Buffer->Payload, 0);
                    $Payload = json_decode($Buffer->Payload);
                    if (fnmatch('*status*', $Buffer->Topic)) {
                        if ($Payload->mode == 'white') {
                            //shellies/shellyrgbw2-2B906F/white/1/status
                            //{"ison":false,"mode":"white","brightness":16,"power":0.00,"overpower":false}
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
                        }
                    }
                }
            }
        }
    }
}
