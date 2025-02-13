# Shelly Dimmer Gen3
   This module enables the integration of a Shelly Dimmer Gen3 in IP-Symcon.
     
   ## Table of Contents
- [Gen3ShellyDimmer](#Gen3ShellyDimmer)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellydimmerg3-deviceid) of the Shelly Dimmer Gen3 is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   **Example:**
   Variable ID State: 12345
   ```php
   RequestAction(12345, true); //Switch On
   RequestAction(12345, false); //Switch On
   ```

   Variable ID Brightness: 56789
   ```php
   RequestAction(56789, 50); //Dim to 50%
   RequestAction(56789, 40); //Dim to 40%
   ```

   ```php
   SHELLY_SetBrightness(int $InstanceID, int $id, int $brightness, int $transition = 0, int $toggle_after = 0);
   ```
   Instance ID: 54321
   ```php
   SHELLY_SetBrightness(54321, 0, 50, 10, 15); //Set brightness to 50% with a transition time of 10 seconds and a "flip-back timer" of 15Sekunden