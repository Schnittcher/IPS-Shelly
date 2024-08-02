# ShellyPro3
   This module enables the integration of a Shelly Pro 3 in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
- [ShellyPro3](#shellypro3)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shelly3pro-deviceid) of the Shelly Pro 3 is entered here.
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID State 1 = 12345
   
   Variable ID State 2 = 56789
   
   Variable ID State 3 = 14725

      ```php
   RequestAction(12345 true); //Switch On State 1
   RequestAction(12345 false); //Switch Off State 1
   
   RequestAction(56789, true); //Switch On State 2
   RequestAction(56789, false); //Switch Off State 2
   
   RequestAction(14725, true); //Switch On State 3
   RequestAction(14725, false); //Switch Off State 3
   ```

   ```php
   SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
   ```
   This function can be used to start a timer.

   **Beispiel:**

   ```php
   SHELLY_ToggleAfter(12345, 0, true, 10); //Switches Relay 0 to on for 10 seconds.
   SHELLY_ToggleAfter(12345, 0, false, 10); //Switches Relay 0 to on after 10 seconds.