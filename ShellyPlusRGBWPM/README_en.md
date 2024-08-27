# ShellyPlusPMMini
   This module enables the integration of a Shelly Plus PM Mini in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
- [ShellyPlusPMMini](#shellypluspmmini)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
## 1. Configuration

Field        | Description
------------ | -------------
MQTT Topic   | The Topic (shellypmmini-deviceid) of the Shelly Plus PM Mini is entered here.

## 2. Functions
```php
RequestAction($VariablenID, $Value);
```

Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

**Beispiel:**
Variable ID Status: 12345
```php
RequestAction(12345, true); //Switch on
RequestAction(12345, false); //Switch off
```

Variable ID Helligkeit: 56789
```php
RequestAction(56789, 50); //set to 50%
RequestAction(56789, 40); //set to 40%
```

Variable ID Weiß: 34567
```php
RequestAction(34567, 50); //set to 50%
RequestAction(34567, 40); //set to S40%
```

Variable ID Farbe: 14725
```php
RequestAction(14725, 0xff0000); //Color red
```

```php
SHELLY_SetLightState(int $InstanceID, int $id, bool $value, int $transition = 0, int $toggle_after = 0);
``` 
This function can be used to set the status of the 4 light channels if the device is set to “Lights x 4”.

```php
SHELLY_SetLightBrightness(int $InstanceID, int $id, int $brightness, int $transition = 0, int $toggle_after = 0);
```
This function can be used to set the brightness of the 4 light channels when the device is set to “Lights x 4”.

```php
SHELLY_SetRGBState(int $InstanceID, int $id, bool $state, int $transition =0, $toggle_after = 0);
```
This function can be used to set the status if the device is set to “RGB”.

```php
SHELLY_SetRGBBrightness(int $InstanceID, int $id, bool $brightness, int $transition =0, $toggle_after = 0);
```
This function can be used to set the brightness when the device is set to “RGB”.

```php
SHELLY_SetRGB(int $InstanceID, int $id, int $brightness, $rgb, int $transition =0, $toggle_after = 0);
```
This function can be used to set the brightness and color when the device is set to “RGB”.

```php
SHELLY_SetRGBW(SetRGBWState(int $InstanceID, int $id, bool $state, int $transition =0, $toggle_after = 0));
```
This function can be used to set the status if the device is set to “RGBW”.

```php
SHELLY_SetRGBWBrightness(int $InstanceID, int $id, bool $brightness, int $transition =0, $toggle_after = 0);
```
This function can be used to set the brightness when the device is set to “RGBW”.

```php
SHELLY_SetRGBWWhite(int $InstanceID, int $id, int $white, int $transition =0, $toggle_after = 0);
```
This function can be used to set the white value if the device is set to “RGBW”.

```php
SHELLY_SetRGBW(int $InstanceID, int $id, int $brightness, $rgb, int $white, int $transition =0, $toggle_after = 0)
```
This function can be used to set the brightness, color and white value when the device is set to “RGBW”.