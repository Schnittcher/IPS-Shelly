<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/ColorHelper.php';

class ShellyPlusRGBWPM extends ShellyModule
{
    use ColorHelper;

    public static $Variables = [

        ['State', 'State', VARIABLETYPE_BOOLEAN, '~Switch', ['rgb', 'rgbw'], '', true, true, false],
        ['Color', 'Color', VARIABLETYPE_INTEGER, '~HexColor', ['rgb', 'rgbw'], '', true, true, false],
        ['Brightness', 'Brightness', VARIABLETYPE_INTEGER, '~Intensity.100', ['rgb', 'rgbw'], '', true, true, false],
        ['White', 'White', VARIABLETYPE_INTEGER, '~Intensity.255', ['rgbw'], '', true, true, false],
        ['Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', ['rgb', 'rgbw'], '', false, true, false],
        ['TotalEnergy', 'Total Energy', VARIABLETYPE_FLOAT, '~Electricity', ['rgb', 'rgbw'], '', false, true, false],
        ['Current', 'Current', VARIABLETYPE_FLOAT, '~Ampere', ['rgb', 'rgbw'], '', false, true, false],
        ['Voltage', 'Voltage', VARIABLETYPE_FLOAT, '~Volt', ['rgb', 'rgbw'], '', false, true, false],
        ['Errors', 'Errors', VARIABLETYPE_STRING, '', ['rgb', 'rgbw'], '', false, true, false],
        ['DeviceTemperature', 'Device Temperature', VARIABLETYPE_FLOAT, '~Temperature', ['rgb', 'rgbw'], '', false, true, false],

        ['LightState0', 'State 1', VARIABLETYPE_BOOLEAN, '~Switch', ['light'], '', true, true, false],
        ['LightBrightness0', 'Brightness 1', VARIABLETYPE_INTEGER, '~Intensity.100', ['light'], '', true, true, false],
        ['LightPower0', 'Power 1', VARIABLETYPE_FLOAT, '~Watt.3680', ['light'], '', false, true, false],
        ['LightTotalEnergy0', 'Total Energy 1', VARIABLETYPE_FLOAT, '~Electricity', ['light'], '', false, true, false],
        ['LightCurrent0', 'Current 1', VARIABLETYPE_FLOAT, '~Ampere', ['light'], '', false, true, false],
        ['LightVoltage0', 'Voltage 1', VARIABLETYPE_FLOAT, '~Volt', ['light'], '', false, true, false],
        ['LightErrors0', 'Errors 1', VARIABLETYPE_STRING, '', ['light'], '', false, true, false],
        ['LightDeviceTemperature0', 'Device Temperature 1', VARIABLETYPE_FLOAT, '~Temperature', ['light'], '', false, true, false],

        ['LightState1', 'State 2', VARIABLETYPE_BOOLEAN, '~Switch', ['light'], '', true, true, false],
        ['LightBrightness1', 'Brightness 2', VARIABLETYPE_INTEGER, '~Intensity.100', ['light'], '', true, true, false],
        ['LightPower1', 'Power 2', VARIABLETYPE_FLOAT, '~Watt.3680', ['light'], '', false, true, false],
        ['LightTotalEnergy1', 'Total Energy 2', VARIABLETYPE_FLOAT, '~Electricity', ['light'], '', false, true, false],
        ['LightCurrent1', 'Current 2', VARIABLETYPE_FLOAT, '~Ampere', ['light'], '', false, true, false],
        ['LightVoltage1', 'Voltage 2', VARIABLETYPE_FLOAT, '~Volt', ['light'], '', false, true, false],
        ['LightErrors1', 'Errors 2', VARIABLETYPE_STRING, '', ['light'], '', false, true, false],
        ['LightDeviceTemperature1', 'Device Temperature 2', VARIABLETYPE_FLOAT, '~Temperature', ['light'], '', false, true, false],

        ['LightState2', 'State 3', VARIABLETYPE_BOOLEAN, '~Switch', ['light'], '', true, true, false],
        ['LightBrightness2', 'Brightness 3', VARIABLETYPE_INTEGER, '~Intensity.100', ['light'], '', true, true, false],
        ['LightPower2', 'Power 3', VARIABLETYPE_FLOAT, '~Watt.3680', ['light'], '', false, true, false],
        ['LightTotalEnergy2', 'Total Energy 3', VARIABLETYPE_FLOAT, '~Electricity', ['light'], '', false, true, false],
        ['LightCurrent2', 'Current 3', VARIABLETYPE_FLOAT, '~Ampere', ['light'], '', false, true, false],
        ['LightVoltage2', 'Voltage 3', VARIABLETYPE_FLOAT, '~Volt', ['light'], '', false, true, false],
        ['LightErrors2', 'Errors 3', VARIABLETYPE_STRING, '', ['light'], '', false, true, false],
        ['LightDeviceTemperature2', 'Device Temperature 3', VARIABLETYPE_FLOAT, '~Temperature', ['light'], '', false, true, false],

        ['LightState3', 'State 4', VARIABLETYPE_BOOLEAN, '~Switch', ['light'], '', true, true, false],
        ['LightBrightness3', 'Brightness 4', VARIABLETYPE_INTEGER, '~Intensity.100', ['light'], '', true, true, false],
        ['LightPower3', 'Power 4', VARIABLETYPE_FLOAT, '~Watt.3680', ['light'], '', false, true, false],
        ['LightTotalEnergy3', 'Total Energy 4', VARIABLETYPE_FLOAT, '~Electricity', ['light'], '', false, true, false],
        ['LightCurrent3', 'Current 4', VARIABLETYPE_FLOAT, '~Ampere', ['light'], '', false, true, false],
        ['LightVoltage3', 'Voltage 4', VARIABLETYPE_FLOAT, '~Volt', ['light'], '', false, true, false],
        ['LightErrors3', 'Errors 4', VARIABLETYPE_STRING, '', ['light'], '', false, true, false],
        ['LightDeviceTemperature3', 'Device Temperature 4', VARIABLETYPE_FLOAT, '~Temperature', ['light'], '', false, true, false],

        
        ['EventComponent', 'Event Component', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Event', 'Event', VARIABLETYPE_STRING, '', [], '', false, true, false],
        ['Reachable', 'Reachable', VARIABLETYPE_BOOLEAN, 'Shelly.Reachable', '', '', false, true, false],
    ];

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Color':
                $rgb = $this->HexToRGB(intval($Value));
                switch ($this->ReadPropertyString('Device')) {
                    case 'rgb':
                        $this->SetRGB(0,$this->GetValue('Brightness'), $rgb, 0, 0);
                        break;
                    case 'rgbw':
                        $this->SetRGBW(0,$this->GetValue('Brightness'), $this->GetValue('White'), $rgb, 0, 0);
                        break;
                }
                break;
            case 'State':
                switch ($this->ReadPropertyString('Device')) {
                    case 'rgb':
                        $this->SetRGBState(0,$Value);
                        break;
                    case 'rgbw':
                        $this->SetRGBWState(0,$Value);
                        break;
                }
                break;
            case 'Brightness':
                switch ($this->ReadPropertyString('Device')) {
                    case 'rgb':
                        $this->SetRGBBrightness(0,$Value);
                        break;
                    case 'rgbw':
                        $this->SetRGBWBrightness(0,$Value);
                        break;
                }
                break;
            case 'White':
                $this->SetRGBWWhite(0,$Value);
                break;
            case 'LightState0':
                $this->SetLightState(0, $Value);
                break;
            case 'LightState1':
                $this->SetLightState(1, $Value);
                break;
            case 'LightState2':
                $this->SetLightState(2, $Value);
                break;
            case 'LightState3':
                $this->SetLightState(3, $Value);
                break;                    
            case 'LightBrightness0':
                $this->SetLightBrightness(0, $Value);
                break;
            case 'LightBrightness1':
                $this->SetLightBrightness(1, $Value);
                break;
            case 'LightBrightness2':
                $this->SetLightBrightness(2, $Value);
                break;
            case 'LightBrightness3':
                $this->SetLightBrightness(3, $Value);
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
                        if (array_key_exists('events', $Payload['params'])) {
                            $events = $Payload['params']['events'][0];
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

                        for ($i = 0; $i <= 3; $i++) {
                            $lightIndex = 'light:' . $i;
                            if (array_key_exists($lightIndex, $Payload['params'])) {
                                $service = $Payload['params'][$lightIndex];
                                if (array_key_exists('output', $service)) {
                                    $this->SetValue('LightState'.$lightIndex, $service['output']);
                                }
                                if (array_key_exists('brightness', $service)) {
                                    $this->SetValue('Brightness'.$lightIndex, $service['brightness']);
                                }
                                if (array_key_exists('apower', $service)) {
                                    $this->SetValue('Power'. $lightIndex, $service['apower']);
                                }
                                if (array_key_exists('voltage', $service)) {
                                    $this->SetValue('Voltage'. $lightIndex, $service['voltage']);
                                }
                                if (array_key_exists('current', $service)) {
                                    $this->SetValue('Current' .$lightIndex, $service['current']);
                                }
                                if (array_key_exists('aenergy', $service)) {
                                    if (array_key_exists('total', $service['aenergy'])) {
                                        $this->SetValue('TotalEnergy'. $lightIndex, $service['aenergy']['total'] / 1000);
                                    }
                                }
                                if (array_key_exists('temperature', $Payload)) {
                                    if (array_key_exists('tC', $Payload['temperature'])) {
                                        $this->SetValue('DeviceTemperature'. $lightIndex, $Payload['temperature']['tC']);
                                    }
                                }
                                if (array_key_exists('errors', $service)) {
                                    $errors = implode(',', $service['errors']);
                                    $this->SetValue('Errors'. $lightIndex, $errors);
                                }
                            }
                            if (array_key_exists('rgb:0', $Payload['params'])) {
                                $service = $Payload['params']['rgb:0'];
                                if (array_key_exists('output', $service)) {
                                    $this->SetValue('State', $service['output']);
                                }
                                if (array_key_exists('brightness', $service)) {
                                    $this->SetValue('Brightness', $service['brightness']);
                                }
                                if (array_key_exists('rgb', $service)) {
                                    $this->SetValue('Color', $this->RGBToHex($service['rgb'][0], $service['rgb'][1], $service['rgb'][2]));
                                }
                                if (array_key_exists('apower', $service)) {
                                    $this->SetValue('Power', $service['apower']);
                                }
                                if (array_key_exists('voltage', $service)) {
                                    $this->SetValue('Voltage', $service['voltage']);
                                }
                                if (array_key_exists('current', $service)) {
                                    $this->SetValue('Current', $service['current']);
                                }
                                if (array_key_exists('aenergy', $service)) {
                                    if (array_key_exists('total', $service['aenergy'])) {
                                        $this->SetValue('TotalEnergy', $service['aenergy']['total'] / 1000);
                                    }
                                }
                                if (array_key_exists('temperature', $Payload)) {
                                    if (array_key_exists('tC', $Payload['temperature'])) {
                                        $this->SetValue('DeviceTemperature', $Payload['temperature']['tC']);
                                    }
                                }
                                if (array_key_exists('errors', $service)) {
                                    $errors = implode(',', $service['errors']);
                                    $this->SetValue('Errors', $errors);
                                }
                            }
                        }
                        if (array_key_exists('rgbw:0', $Payload['params'])) {
                            $service = $Payload['params']['rgbw:0'];
                            if (array_key_exists('output', $service)) {
                                $this->SetValue('State', $service['output']);
                            }
                            if (array_key_exists('brightness', $service)) {
                                $this->SetValue('Brightness', $service['brightness']);
                            }
                            if (array_key_exists('rgb', $service)) {
                                $this->SetValue('Color', $this->RGBToHex($service['rgb'][0], $service['rgb'][1], $service['rgb'][2]));
                            }
                            if (array_key_exists('white', $service)) {
                                $this->SetValue('White', $service['white']);
                            }
                            if (array_key_exists('apower', $service)) {
                                $this->SetValue('Power', $service['apower']);
                            }
                            if (array_key_exists('voltage', $service)) {
                                $this->SetValue('Voltage', $service['voltage']);
                            }
                            if (array_key_exists('current', $service)) {
                                $this->SetValue('Current', $service['current']);
                            }
                            if (array_key_exists('aenergy', $service)) {
                                if (array_key_exists('total', $service['aenergy'])) {
                                    $this->SetValue('TotalEnergy', $service['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('temperature', $Payload)) {
                                if (array_key_exists('tC', $Payload['temperature'])) {
                                    $this->SetValue('DeviceTemperature', $Payload['temperature']['tC']);
                                }
                            }
                            if (array_key_exists('errors', $service)) {
                                $errors = implode(',', $service['errors']);
                                $this->SetValue('Errors', $errors);
                            }
                        }
                    }
                    }
                }
            }
        }

    public function SetLightState(int $id, bool $value, int $transition = 0, int $toggle_after = 0)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Light.Set';
        $Payload['params'] = ['id' => $id, 'on' => $value];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetLightBrightness(int $id, int $brightness, int $transition = 0, int $toggle_after = 0)
    {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'Light.Set';
        $Payload['params'] = ['id' => $id, 'on' => true, 'brightness' => $brightness];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGBState(int $id, bool $state, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGB.Set';
        $Payload['params'] = ['id' => $id, 'on' => true];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGBBrightness(int $id, int $brightness, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGB.Set';
        $Payload['params'] = ['id' => $id, 'on' => true, 'brightness' => $brightness];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGB(int $id, int $brightness, $rgb, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGB.Set';
        $Payload['params'] = ['id' => $id, 'on' => true, 'brightness' => $brightness, 'rgb' => $rgb];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGBWState(int $id, bool $state, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGBW.Set';
        $Payload['params'] = ['id' => $id, 'on' => true];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGBWBrightness(int $id, int $brightness, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGBW.Set';
        $Payload['params'] = ['id' => $id, 'on' => true, 'brightness' => $brightness];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGBWWhite(int $id, int $white, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGBW.Set';
        $Payload['params'] = ['id' => $id, 'on' => true, 'white' => $white];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }

    public function SetRGBW(int $id, int $brightness, $rgb, int $white, int $transition =0, $toggle_after = 0) {
        $Topic = $this->ReadPropertyString('MQTTTopic') . '/rpc';

        $Payload['id'] = 1;
        $Payload['src'] = 'user_1';
        $Payload['method'] = 'RGBW.Set';
        $Payload['params'] = ['id' => $id, 'on' => true, 'brightness' => $brightness, 'rgb' => $rgb, 'white' => $white];
        if ($toggle_after != 0) {
            $Payload['params']['toggle_after'] = $toggle_after;
        }
        if ($transition != 0) {
            $Payload['params']['transition_duration'] = $transition;
        }

        $this->sendMQTT($Topic, json_encode($Payload));
    }
}
