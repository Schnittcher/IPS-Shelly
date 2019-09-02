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
   
   ### Shelly_SwitchMode($InstanceID, $Relay, $Value)
   It is possible to switch the device on or off with this function.
   ```php
   Shelly_SwitchMode(25537, 0, true); //Switch On Relay;
   Shelly_SwitchMode(25537, 0, false); //Switch Off Relay;
   ```