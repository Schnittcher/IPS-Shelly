<?php

define('MQTT_GROUP_TOPIC', 'shellies');

trait ShellyRelayAction {

    public function RequestAction($Ident, $Value)
    {
        if ($Ident == 'Shelly_Power') {
            $relay = 0;
        } else {
            $relay = substr($Ident, -1, 1);
        }

        $this->SendDebug(__FUNCTION__ . ' Relay', $relay, 0);
        $this->SendDebug(__FUNCTION__ . ' Value', $Value, 0);
        $result = $this->SwitchMode($relay, $Value);
    }

    public function SwitchMode(int $relay, bool $Value)
    {
        $Buffer['Topic'] = MQTT_GROUP_TOPIC.'/'.$this->ReadPropertyString('MQTTTopic').'/relay/'.$relay.'/command';
        if($Value) {
            $Buffer['MSG'] = 'on';
        } else {
            $Buffer['MSG'] = 'off';
        }
        $BufferJSON = json_encode($Buffer);
        $this->SendDebug(__FUNCTION__, $BufferJSON, 0);
        $this->SendDataToParent(json_encode(array('DataID' => '{018EF6B5-AB94-40C6-AA53-46943E824ACF}', 'Action' => 'Publish', 'Buffer' => $BufferJSON)));
    }
}