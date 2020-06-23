# ShellyAir
   This module enables the integration of a ShellyAir in IP-Symcon.
     
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shelly1-deviceid) of the Shelly1 is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   **Example:**
   
   Variable ID State: 12345
   ```php
   RequestAction(12345, true); //Switch on
   RequestAction(12345, false); //Switch off
   ```