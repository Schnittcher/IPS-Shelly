<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusSmoke extends ShellyModule
{
    public static $Variables = [
        ['Alarm', 'Alarm', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true],
        ['Mute', 'Mute', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true],
        ['Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true],
        ['BatteryVolt', 'Battery Volt', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Mute':
                $this->Mute(0, $Value);
                break;
            }
    }

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
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('smoke:0', $Payload['params'])) {
                            $this->SetValue('Alarm', $Payload['params']['smoke:0']['alarm']);
                            $this->SetValue('Mute', $Payload['params']['smoke:0']['mute']);
                        }
                        if (array_key_exists('devicepower:0', $Payload['params'])) {
                            $this->SetValue('Battery', $Payload['params']['devicepower:0']['battery']['percent']);
                            $this->SetValue('BatteryVolt', $Payload['params']['devicepower:0']['battery']['V']);
                        }
                    }
                }
            }
        }
    }
    private function Mute(int $id, bool $value)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Smoke.Mute';
        $Payload['params'] = ['id' => $switch, 'mute' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}
