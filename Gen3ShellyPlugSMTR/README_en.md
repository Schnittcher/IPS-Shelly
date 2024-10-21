# Gen3ShellyPlugSMTR
   This module enables the integration of a Shelly Plug S MTR Gen3 in IP-Symcon.\
       
## Table of Contents
- [Gen3ShellyPlugSMTR](#gen3shellyplugsmtr)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
## 1. Configuration
  
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic of the Shelly Plug S MTR Gen3 is entered here.
   
## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID State = 12345
   
   ```php
   RequestAction(12345 true); //Switch On 
   RequestAction(12345 false); //Switch Off
   ```

   ```php
   SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
   ```
   This function can be used to start a timer.

   **Beispiel:**

   ```php
   SHELLY_ToggleAfter(12345, 0, true, 10); //Switches Relay 0 to on for 10 seconds.
   SHELLY_ToggleAfter(12345, 0, false, 10); //Switches Relay 0 to on after 10 seconds.