# Gen3Shelly1Mini
   This module enables the integration of a en 3 Shelly 1 Mini, Shelly 1 PM Mini or Shelly PM Mini in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
## Table of Contents
- [Gen3Shelly1Mini](#gen3shelly1mini)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)

## 1. Configuration

Field        | Description
------------ | -------------
MQTT Topic   | The Topic of the Shelly Plus 1PM is entered here.
Device       | The Type of the Shelly.

## 2. Functions

```php
RequestAction($VariablenID, $Value);
```
It´s possible to use all variable actions with this function.

**Example:**

Variable ID State 1 = 12345

```php
RequestAction(12345 true); //Switch On State 1
RequestAction(12345 false); //Switch Off State 1

```

```php
SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
```
This function can be used to start a timer.

**Beispiel:**

```php
SHELLY_ToggleAfter(12345, 0, true, 10); //Switches Relay 0 to on for 10 seconds.
SHELLY_ToggleAfter(12345, 0, false, 10); //Switches Relay 0 to on after 10 seconds.