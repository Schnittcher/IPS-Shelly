<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusSmoke extends ShellyModule
{
    use ShellyGen2Plus;

    public static $Variables = [
        ['Alarm', 'Alarm', VARIABLETYPE_BOOLEAN, '~Alert', [], '', false, true, false],
        ['Mute', 'Mute', VARIABLETYPE_BOOLEAN, '~Switch', [], '', true, true, false],
        ['Battery', 'Battery', VARIABLETYPE_INTEGER, '~Battery.100', [], '', false, true, false],
        ['BatteryVolt', 'Battery Volt', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false]
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
                    if (!$Payload) {
                        $this->zeroingValues();
                    }
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('smoke:0', $Payload['params'])) {
                            if (array_key_exists('alarm', $Payload['params']['smoke:0'])) {
                                $this->SetValue('Alarm', $Payload['params']['smoke:0']['alarm']);
                            }
                            if (array_key_exists('mute', $Payload['params']['smoke:0'])) {
                                $this->SetValue('Mute', $Payload['params']['smoke:0']['mute']);
                            }
                        }
                        if (array_key_exists('devicepower:0', $Payload['params'])) {
                            $this->SetValue('Battery', $Payload['params']['devicepower:0']['battery']['percent']);
                            $this->SetValue('BatteryVolt', $Payload['params']['devicepower:0']['battery']['V']);
                        }
                    }
                }
                if (fnmatch('*/status/smoke:0', $Buffer['Topic'])) {
                    if (array_key_exists('alarm', $Payload)) {
                        $this->SetValue('Alarm', $Payload['alarm']);
                    }
                    if (array_key_exists('mute', $Payload)) {
                        $this->SetValue('Mute', $Payload['mute']);
                    }
                }
                if (fnmatch('*/status/devicepower:0', $Buffer['Topic'])) {
                    if (array_key_exists('battery', $Payload)) {
                        if (array_key_exists('percent', $Payload['battery'])) {
                            $this->SetValue('Battery', $Payload['battery']['percent']);
                        }
                        if (array_key_exists('V', $Payload['battery'])) {
                            $this->SetValue('BatteryVolt', $Payload['battery']['V']);
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
        $Payload['params'] = ['id' => $id, 'mute' => $value];

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}
