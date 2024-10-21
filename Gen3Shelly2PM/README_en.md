# Gen3Shelly2PM
   This module enables the integration of a Gen 3 Shelly 2 PM in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
## Table of Contents
- [Gen3Shelly2PM](#gen3shelly2pm)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
    - [2.1 Relay](#21-relay)
    - [2.2 Shutter](#22-shutter)

## 1. Configuration

Field        | Description
------------ | -------------
MQTT Topic   | The Topic of the Shelly Plus 2PM is entered here.
Device       | The Type of the Shelly.

   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   ### 2.1 Relay

   **Example:**

   Variable ID State 1  = 12345

   Variable ID State 2  = 56789
   ```php
   RequestAction(12345, true); //Switch On State 1
   RequestAction(12345, false); //Switch Off State 1

   RequestAction(56789, true); //Switch On State 1
   RequestAction(56789, false); //Switch Off State 1
   ```
   
  ### 2.2 Shutter

   **Example:**
   
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

   Variable ID SLkat Position = 56788
   ```php
   RequestAction(56788, 25);  //Move slatz position to 25%
   ```
      

```php
SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
```
This function can be used to start a timer.

**Beispiel:**

```php
SHELLY_ToggleAfter(12345, 0, true, 10); //Switches Relay 0 to on for 10 seconds.
SHELLY_ToggleAfter(12345, 0, false, 10); //Switches Relay 0 to on after 10 seconds.