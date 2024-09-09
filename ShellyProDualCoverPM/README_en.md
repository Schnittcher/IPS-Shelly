# ShellyProDualCoverPM
This module enables the integration of a Shelly Pro Dual Cover PM in IP-Symcon.
   
## Table of Contents
- [ShellyProDualCoverPM](#shellyprodualcoverpm)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)

## 1. Configuration

Field        | Description
------------ | -------------
MQTT Topic   | The Topic (shellyprodualcoverpm-deviceid) of the Shelly Pro Dual Cover PM is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!

## 2. Functions

Variable ID Shutter = 12345
```php
RequestAction(12345, 0);  //Move shutter up
RequestAction(12345, 2); //Stop shutter
RequestAction(12345, 4); ////Move shutter down

```

Variable ID Position = 56789
```php
RequestAction(56789, 25);  //Move shutter to 25%
```