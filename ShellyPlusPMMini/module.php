<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/ShellyModule.php';

class ShellyPlusPMMini extends ShellyModule
{
    public static $Variables = [
        ['Current', 'Current', VARIABLETYPE_FLOAT, '~Ampere', [], '', false, true, false],
        ['Voltage', 'Voltage', VARIABLETYPE_FLOAT, '~Volt', [], '', false, true, false],
        ['Power', 'Power', VARIABLETYPE_FLOAT, '~Watt.3680', [], '', false, true, false],
        //['AprtPower', 'Apparent Power', VARIABLETYPE_FLOAT, '~Watt', [], '', false, true, false],
        //['PF', 'Power Factor', VARIABLETYPE_FLOAT, '', [], '', false, true, false],
        ['Frequency ', 'Frequency', VARIABLETYPE_FLOAT, '~Hertz', [], '', false, true, false],
        ['TotalEnergy', 'Total Energy', VARIABLETYPE_FLOAT, '~Electricity', [], '', false, true, false],
        ['Error', 'Error', VARIABLETYPE_STRING, '', [], '', false, true, false],
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

                if (fnmatch('*/status/pm1:*', $Buffer['Topic'])) {
                    if (array_key_exists('id', $Payload)) {
                        if ($Payload['id'] == 0) {
                            $this->SetValue('Current', $Payload['current']);
                            $this->SetValue('Voltage', $Payload['voltage']);
                            $this->SetValue('Power', $Payload['apower']);
                            //$this->SetValue('AprtPower', $Payload['aprtpower']);
                            //$this->SetValue('PF', $Payload['pf']);
                            $this->SetValue('Frequency', $Payload['freq']);
                            if (array_key_exists('aenergy', $Payload)) {
                                if (array_key_exists('total', $Payload['aenergy'])) {
                                    $this->SetValue('TotalEnergy', $Payload['aenergy']['total'] / 1000);
                                }
                            }
                            if (array_key_exists('errors', $Payload)) {
                                if (!empty($Payload['errors'])) {
                                    $this->SetValue('Error', implode(',', $Payload['errors']));
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
