<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusi4 extends ShellyModule
{
    public static $Variables = [
        ['Input0', 'Input 1', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input1', 'Input 2', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input2', 'Input 3', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['Input3', 'Input 4', VARIABLETYPE_BOOLEAN, '~Switch', [], '', false, true],
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true],
        ['Shelly_Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true]
    ];

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (!empty($this->ReadPropertyString('MQTTTopic'))) {
            $data = json_decode($JSONString, true);

            switch ($data['DataID']) {
                case '{7F7632D9-FA40-4F38-8DEA-C83CD4325A32}': // MQTT Server
                    $Buffer = $data;
                    break;
                case '{DBDA9DF7-5D04-F49D-370A-2B9153D00D9B}': //MQTT Client
                    $Buffer = json_decode($data['Buffer']);
                    break;
                default:
                    $this->LogMessage('Invalid Parent', KL_ERROR);
                    return;
            }

            $this->SendDebug('MQTT Topic', $Buffer['Topic'], 0);

            $Payload = json_decode($Buffer['Payload'], true);
            if (array_key_exists('Topic', $Buffer)) {
                if (fnmatch('*/online', $Buffer['Topic'])) {
                    $this->SetValue('Reachable', $Payload);
                }
                if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                    if (array_key_exists('params', $Payload)) {
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'];
                            $this->SetValue('EventComponent', $events['component']);
                            $this->SetValue('Event', $events['event']);
                        }
                        for ($i = 0; $i <= 3; $i++) {
                            $inputIndex = 'input:' . $i;
                            if (array_key_exists($inputIndex, $Payload['params'])) {
                                $input = $Payload['params'][$inputIndex];
                                if (array_key_exists('state', $input)) {
                                    $this->SetValue('Input' . $i, $input['state']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
