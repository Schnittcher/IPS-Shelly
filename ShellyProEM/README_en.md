# ShellyProEM
   This module enables the integration of a Shelly Pro EM in IP-Symcon.\
    
## Table of Contents
- [ShellyProEM](#shellyproem)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyproem50-deviceid) of the Shelly Pro EM is entered here.
   
## 2. Functions

   ```php
   SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
   ```
   This function can be used to start a timer.

   **Beispiel:**

   ```php
   SHELLY_ToggleAfter(12345, 0, true, 10); //Switches Relay 0 to on for 10 seconds.
   SHELLY_ToggleAfter(12345, 0, false, 10); //Switches Relay 0 to on after 10 seconds.
   ```