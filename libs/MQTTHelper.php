<?php

declare(strict_types=1);
define('MQTT_GROUP_TOPIC', 'shellies');
trait MQTTHelper
{
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
        $ServerJSON = json_encode($Server);
        $this->SendDebug(__FUNCTION__ . 'MQTT Server', $ServerJSON, 0);
        $resultServer = @$this->SendDataToParent($ServerJSON);

        //MQTT Client
        $Buffer['PacketType'] = 3;
        $Buffer['QualityOfService'] = 0;
        $Buffer['Retain'] = false;
        $Buffer['Topic'] = $Topic;
        $Buffer['Payload'] = $Payload;
        //$BufferJSON = json_encode($Buffer, JSON_UNESCAPED_SLASHES);

        $Client['DataID'] = '{97475B04-67C3-A74D-C970-E9409B0EFA1D}';
        $Client['Buffer'] = $Buffer;

        $ClientJSON = json_encode($Client);
        $this->SendDebug(__FUNCTION__ . 'MQTT Client', $ClientJSON, 0);
        $resultClient = @$this->SendDataToParent($ClientJSON);

        if ($resultServer === false && $resultClient === false) {
            $last_error = error_get_last();
            echo $last_error['message'];
        }
    }
}