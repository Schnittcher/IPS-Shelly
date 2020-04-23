# ShellyPlug
   This module enables the integration of a ShellyPlug in IP-Symcon.\
   The relay can be switched and the sensor data is visualized in IP-Symcon.   
      
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyplug-deviceid) of the ShellyPlug is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
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