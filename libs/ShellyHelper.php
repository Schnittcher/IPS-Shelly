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
        $Buffer['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/relay/' . $relay . '/command';
        if ($Value) {
            $Buffer['MSG'] = 'on';
        } else {
            $Buffer['MSG'] = 'off';
        }
        $BufferJSON = json_encode($Buffer);
        $this->SendDebug(__FUNCTION__, $BufferJSON, 0);
        $this->SendDataToParent(json_encode(['DataID' => '{018EF6B5-AB94-40C6-AA53-46943E824ACF}', 'Action' => 'Publish', 'Buffer' => $BufferJSON]));
    }
}

trait ShellyRollerAction
{
    public function MoveDown()
    {
        $Buffer['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Buffer['MSG'] = 'close';
        $BufferJSON = json_encode($Buffer);
        $this->SendDebug(__FUNCTION__, $BufferJSON, 0);
        $this->SendDataToParent(json_encode(['DataID' => '{018EF6B5-AB94-40C6-AA53-46943E824ACF}', 'Action' => 'Publish', 'Buffer' => $BufferJSON]));
    }

    public function MoveUp()
    {
        $Buffer['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Buffer['MSG'] = 'open';
        $BufferJSON = json_encode($Buffer);
        $this->SendDebug(__FUNCTION__, $BufferJSON, 0);
        $this->SendDataToParent(json_encode(['DataID' => '{018EF6B5-AB94-40C6-AA53-46943E824ACF}', 'Action' => 'Publish', 'Buffer' => $BufferJSON]));
    }

    public function Move($position)
    {
        $Buffer['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command/pos';
        $Buffer['MSG'] = $position;
        $BufferJSON = json_encode($Buffer);
        $this->SendDebug(__FUNCTION__, $BufferJSON, 0);
        $this->SendDataToParent(json_encode(['DataID' => '{018EF6B5-AB94-40C6-AA53-46943E824ACF}', 'Action' => 'Publish', 'Buffer' => $BufferJSON]));
    }

    public function Stop()
    {
        $Buffer['Topic'] = MQTT_GROUP_TOPIC . '/' . $this->ReadPropertyString('MQTTTopic') . '/roller/0/command';
        $Buffer['MSG'] = 'stop';
        $BufferJSON = json_encode($Buffer);
        $this->SendDebug(__FUNCTION__, $BufferJSON, 0);
        $this->SendDataToParent(json_encode(['DataID' => '{018EF6B5-AB94-40C6-AA53-46943E824ACF}', 'Action' => 'Publish', 'Buffer' => $BufferJSON]));
    }
}
