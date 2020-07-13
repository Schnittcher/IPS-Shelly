# ShellySmoke
   This module enables the integration of the Shelly Smoke in IP-Symcon.\
   The sensor values of a Shelly Smoke are transmitted into IP-Symcon.
     
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellysmoke-deviceid) of the Shelly Smoke is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   **Example:**
   
   Variable ID Control: 12345
   
   ```php
   RequestAction(12345, 0); //Self Test
   RequestAction(12345, 1); //Mute
   RequestAction(12345, 2); //Unmute
   ```