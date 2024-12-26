<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyBLUGateway extends ShellyModule
{
    public static $Variables = [
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false],
    ];

    public function ReceiveData($JSONString)
    {
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $Buffer = json_decode($JSONString, true);
            $this->SendDebug('JSON', $Buffer, 0);

            //Für MQTT Fix in IPS Version 6.3
            if (IPS_GetKernelDate() > 1670886000) {
                $Buffer['Payload'] = utf8_decode($Buffer['Payload']);
            }

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                    if (!$Payload) {
                        $this->zeroingValues();
                    }
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'][0];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
                        }
                    }
                }
            }
        }
    }
}
