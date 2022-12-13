[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/IPS-Shelly/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/IPS-Shelly/actions)

# IPS-Shelly
   This module enables the implementation of the devices from Shelly in IP-Symcon.
 
## Table of Contents
- [IPS-Shelly](#ips-shelly)
  - [Table of Contents](#table-of-contents)
  - [1. Requirements](#1-requirements)
    - [1.1 Enabling MQTT](#11-enabling-mqtt)
  - [2. Included Modules](#2-included-modules)
  - [3. Installation](#3-installation)
  - [4. Configuration in IP-Symcon](#4-configuration-in-ip-symcon)
  - [5. Donate](#5-donate)
  - [6. License](#6-license)
   
## 1. Requirements

* at least IPS Version 5.1
* enabled MQTT protocol on each shelly device

### 1.1 Enabling MQTT
It is required to enable MQTT on each shelly device to allow communication with IP-Symcon. Configuration is done through the Shelly web-interface:

Internet & Security -> ADVANCED - DEVELOPER SETTINGS -> Enable action execution via MQTT

Please enter the IP and MQTT port of IP-Symcon in the server field. The default port for MQTT is 1883. If you changed the port within IP-Symcon you can check the configured port under I/O instances -> Server Socket (MQTT Server #InstanceID).

If you would like to use the username/password settings you need to configure it in Splitter instances -> MQTT server. The same username/password must be used for each shelly device on the web-interface: Internet & Security -> ADVANCED - DEVELOPER SETTINGS.

## 2. Included Modules

* [Shelly1](Shelly1/README_en.md)
* [Shelly2](Shelly2/README_en.md)
* [Shelly3EM](Shelly3EM/README_en.md)
* [Shelly4Pro](Shelly4Pro/README_en.md)
* [ShellyAir](ShellyAir/README_en.md)
* [ShellyBulb](ShellyBulb/README_en.md)
* [ShellyButton1](ShellyButton1/README_en.md)
* [ShellyConfigurator](ShellyConfigurator/README_en.md)
* [ShellyDimmer](ShellyDimmer/README_en.md)
* [ShellyDuo](ShellyDuo/README_en.md)
* [ShellyEM](ShellyEM/README_en.md)
* [ShellyFlood](ShellyFlood/README_en.md)
* [ShellyGas](ShellyGas/README_en.md)
* [ShellyHT](ShellyHT/README_en.md)
* [Shellyi3](Shellyi3/README_en.md)
* [ShellyMotion](ShellyMotion/README_en.md)
* [ShellyMotion 2](ShellyMotion2/README_en.md)
* [ShellyPlug](ShellyPlug/README_en.md)
* [ShellyPlus1](ShellyPlus1/README_en.md)
* [ShellyPlus2PM](ShellyPlus2PM/README_en.md)
* [ShellyPlusHT](ShellyPlusHT/README_en.md)
* [ShellyPlusi4](ShellyPlusi4/README_en.md)
* [ShellyPro1](ShellyPro1/README_en.md)
* [ShellyPro2](ShellyPro2/README_en.md)
* [ShellyPro3](ShellyPro3/README_en.md)
* [ShellyPro4PM](ShellyPro4PM/README_en.md)
* [ShellyRGBW2](ShellyRGBW2/README_en.md)
* [ShellySense](ShellySense/README_en.md)
* [ShellySmoke](ShellySmoke/README_en.md)
* [ShellyTRV](ShellyTRV/README_en.md)
* [ShellyUni](ShellyUni/README_en.md)
* [ShellyVintge](ShellyVintage/README_en.md)
* [ShellyWindow](ShellyWindow/README_en.md)

## 3. Installation
Installation via the IP-Symcon module store.

## 4. Configuration in IP-Symcon
Check the individual modules please.

## 5. Donate

This module is free for non-commercial use. Gifts as support for the author are accepted here:

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. License

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
