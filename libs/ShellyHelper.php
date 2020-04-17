<?php

declare(strict_types=1);

if (!function_exists('fnmatch')) {
    function fnmatch($pattern, $string)
    {
        return preg_match('#^' . strtr(preg_quote($pattern, '#'), ['\*' => '.*', '\?' => '.']) . '$#i', $string);
    }
}

trait Shelly
{
    protected function getChannelRelay(string $topic)
    {
        $ShellyTopic = explode('/', $topic);
        $LastKey = count($ShellyTopic) - 1;
        $relay = $ShellyTopic[$LastKey];
        return $relay;
    }

    protected function getChannel(string $topic)
    {
        $ShellyTopic = explode('/', $topic);
        $LastKey = count($ShellyTopic) - 2;
        $relay = $ShellyTopic[$LastKey];
        return $relay;
    }

    protected function rgbToHex($r, $g, $b)
    {
        return ($r << 16) + ($g << 8) + $b;
    }

    protected function HexToRGB(int $value)
    {
        $RGB = [];
        $RGB[0] = (($value >> 16) & 0xFF);
        $RGB[1] = (($value >> 8) & 0xFF);
        $RGB[2] = ($value & 0xFF);
        return $RGB;
    }
}