<?php

declare(strict_types=1);
define('MQTT_GROUP_TOPIC', 'shellies');

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
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/' . $relay . '/command';

        if ($Value) {
            $Data['Payload'] = 'on';
        } else {
            $Data['Payload'] = 'off';
        }
        $DataJSON = json_encode($Data,JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__. 'Topic', $Data['Topic'],0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }
}

trait ShellyRollerAction
{
    public function MoveDown()
    {
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Data['Payload'] = 'close';
        $DataJSON = json_encode($Data,JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__. 'Topic', $Data['Topic'],0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }

    public function MoveUp()
    {
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Data['Payload'] = 'open';
        $DataJSON = json_encode($Data,JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__. 'Topic', $Data['Topic'],0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }

    public function Move($position)
    {
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Data['Payload'] = $position;
        $DataJSON = json_encode($Data,JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__. 'Topic', $Data['Topic'],0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }

    public function Stop()
    {
        $Data['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Data['PacketType'] = 3;
        $Data['QualityOfService'] = 0;
        $Data['Retain'] = false;
        $Data['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Data['Payload'] = 'stop';
        $DataJSON = json_encode($Data,JSON_UNESCAPED_SLASHES);
        $this->SendDebug(__FUNCTION__. 'Topic', $Data['Topic'],0);
        $this->SendDebug(__FUNCTION__, $DataJSON, 0);
        $this->SendDataToParent($DataJSON);
    }
}
