<?php

declare(strict_types=1);
define('MQTT_GROUP_TOPIC', 'shellies');

if (!function_exists('fnmatch')) {
    function fnmatch($pattern, $string)
    {
        return preg_match('#^' . strtr(preg_quote($pattern, '#'), ['\*' => '.*', '\?' => '.']) . '$#i', $string);
    }
}

trait Shelly
{
    protected function getChannelRelay(string $topic)
    {
        $ShellyTopic = explode('/', $topic);
        $LastKey = count($ShellyTopic) - 1;
        $relay = $ShellyTopic[$LastKey];
        return $relay;
    }

    protected function getChannel(string $topic)
    {
        $ShellyTopic = explode('/', $topic);
        $LastKey = count($ShellyTopic) - 2;
        $relay = $ShellyTopic[$LastKey];
        return $relay;
    }

    protected function sendMQTT($Topic, $Payload)
    {
        $resultServer = true;
        $resultClient = true;
        //MQTT Server
        $Server['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Server['PacketType'] = 3;
        $Server['QualityOfService'] = 0;
        $Server['Retain'] = false;
        $Server['Topic'] = $Topic;
        $Server['Payload'] = $Payload;
        $ServerJSON = json_encode($Server, JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__ . 'MQTT Server', $ServerJSON, 0);
        $resultServer = @$this->SendDataToParent($ServerJSON);

        //MQTT Client
        $Buffer['PacketType'] = 3;
        $Buffer['QualityOfService'] = 0;
        $Buffer['Retain'] = false;
        $Buffer['Topic'] = $Topic;
        $Buffer['Payload'] = $Payload;
        $BufferJSON = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

        $Client['DataID'] = '{97475B04-67C3-A74D-C970-E9409B0EFA1D}';
        $Client['Buffer'] = $BufferJSON;

        $ClientJSON = json_encode($Client);
        $this->SendDebug(__FUNCTION__ . 'MQTT Client', $ClientJSON, 0);
        $resultClient = @$this->SendDataToParent($ClientJSON);

        if ($resultServer === false && $resultClient === false) {
            $last_error = error_get_last();
            echo $last_error['message'];
        }
    }

    private function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, $Vartype); // 0 boolean, 1 int, 2 float, 3 string,
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != $Vartype) {
                $this->SendDebug('Profile', 'Variable profile type does not match for profile ' . $Name, 0);
            }
        }
        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
        IPS_SetVariableProfileValues(
            $Name, $MinValue, $MaxValue, $StepSize
        ); // string $ProfilName, float $Minimalwert, float $Maximalwert, float $Schrittweite
    }

    private function RegisterProfileAssociation($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype, $Associations)
    {
        if (is_array($Associations) && count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        }
        $this->RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype);
        if (is_array($Associations)) {
            foreach ($Associations as $Association) {
                IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
            }
        } else {
            $Associations = $this->$Associations;
            foreach ($Associations as $code => $association) {
                IPS_SetVariableProfileAssociation($Name, $code, $this->Translate($association), $Icon, -1);
            }
        }
    }
}

trait ShellyRelayAction
{
    public function RequestAction($Ident, $Value)
    {
        if (fnmatch('Shelly_State*', $Ident)) {
            if ($Ident == 'Shelly_State') {
                $relay = 0;
            } else {
                $relay = substr($Ident, -1, 1);
            }
            $this->SendDebug(__FUNCTION__ . ' Relay', $relay, 0);
            $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
            $this->SwitchMode(intval($relay), $Value);
            return;
        }
        if ($Ident == 'Shelly_Roller') {
            switch ($Value) {
                case 0:
                    $this->MoveUp();
                    break;
                case 2:
                    $this->Stop();
                    break;
                case 4:
                    $this->MoveDown();
                    break;
                default:
                    $this->SendDebug(__FUNCTION__ . 'Ident: Shelly_Roller', 'Invalid Value:' . $Value, 0);
            }
            return;
        }
        if ($Ident == 'Shelly_RollerPosition') {
            $this->SendDebug(__FUNCTION__ . ' Value Shelly_RollerPosition', $Value, 0);
            $this->Move($Value);
            return;
        }
        $this->SendDebug(__FUNCTION__, 'No Action - Ident: ' . $Ident, 0);
    }

    public function SwitchMode(int $relay, bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/' . $relay . '/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }
}

trait ShellyRollerAction
{
    public function MoveDown()
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Payload = 'close';
        $this->sendMQTT($Topic, $Payload);
    }

    public function MoveUp()
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Payload = 'open';
        $this->sendMQTT($Topic, $Payload);
    }

    public function Move($position)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command/pos';
        $Payload = strval($position);
        $this->sendMQTT($Topic, $Payload);
    }

    public function Stop()
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Payload = 'stop';
        $this->sendMQTT($Topic, $Payload);
    }
}
trait ShellyRGBW2Action
{
    public function RequestAction($Ident, $Value)
    {
        if (fnmatch('Shelly_State*', $Ident)) {
            if ($Ident == 'Shelly_State') {
                $relay = 0;
            } else {
                $relay = substr($Ident, -1, 1);
            }
            $this->SendDebug(__FUNCTION__ . ' Channel', $relay, 0);
            $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
            $this->SwitchMode(intval($relay), $Value);
            return;
        }
        if (fnmatch('Shelly_Brightness*', $Ident)) {
            if ($Ident == 'Shelly_Brightness') {
                $relay = 0;
            } else {
                $relay = substr($Ident, -1, 1);
            }
            $this->SendDebug(__FUNCTION__ . ' Channel', $relay, 0);
            $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
            $this->setDimmer(intval($relay), $Value);
            return;
        }
        if (fnmatch('Shelly_Color', $Ident)) {
            $this->setColor($Value);
        }
        if (fnmatch('Shelly_White', $Ident)) {
            $this->setWhite($Value);
        }
        if (fnmatch('Shelly_Gain', $Ident)) {
            $this->setGain($Value);
        }
        if (fnmatch('Shelly_Effect', $Ident)) {
            $this->setEffect($Value);
        }
    }

    public function setDimmer(int $channel, int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/white/' . $channel . '/set';
        $Payload['brightness'] = strval($value);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }

    public function setColor(string $color)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';

        $RGB = $this->HexToRGB($color);
        $Payload['red'] = strval($RGB[0]);
        $Payload['green'] = strval($RGB[1]);
        $Payload['blue'] = strval($RGB[2]);

        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    public function setGain(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['gain'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    public function setWhite(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['white'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    public function setEffect(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/color/0/set';
        $Payload['effect'] = strval($value);
        $Payload = json_encode($Payload);

        $this->sendMQTT($Topic, $Payload);
    }

    public function SwitchMode(int $relay, bool $Value)
    {
        $Mode = strtolower($this->ReadPropertyString('Mode'));
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/' . $Mode . '/' . $relay . '/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }

        $this->sendMQTT($Topic, $Payload);
    }

    protected function rgbToHex($r, $g, $b)
    {
        return ($r << 16) + ($g << 8) + $b;
    }

    protected function HexToRGB($value)
    {
        $RGB = [];
        $RGB[0] = (($value >> 16) & 0xFF);
        $RGB[1] = (($value >> 8) & 0xFF);
        $RGB[2] = ($value & 0xFF);
        return $RGB;
    }
}

trait ShellyDimmerAction
{
    public function RequestAction($Ident, $Value)
    {
        if (fnmatch('Shelly_State', $Ident)) {
            $this->DimmerSwitchMode($Value);
        }
        if (fnmatch('Shelly_Brightness', $Ident)) {
            $this->DimSet(intval($Value));
        }
    }

    public function DimmerSwitchMode(bool $Value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/light/0/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->sendMQTT($Topic, $Payload);
    }

    public function DimSet(int $value)
    {
        $Topic = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/light/0/set';
        $Payload['brightness'] = strval($value);
        $Payload = json_encode($Payload);
        $this->sendMQTT($Topic, $Payload);
    }
}
