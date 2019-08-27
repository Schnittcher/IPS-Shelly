# Shelly4Pro
   This module enables the integration of a Shelly4Pro in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyswitch-deviceid) of the Shelly4Pro is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions
   
   **Shelly_SwitchMode($InstanceID, $Relay, $Value)**\
   It is possible to switch the device on or off with this function.
   ```php
   Shelly_SwitchMode(25537, 0, true); //Switch On Relay 1
   Shelly_SwitchMode(25537, 0, false); //Switch Off Relay 1
   
   Shelly_SwitchMode(25537, 1, true); //Switch On Relay 2
   Shelly_SwitchMode(25537, 1, false); //Switch Off Relay 2
   
   Shelly_SwitchMode(25537, 2, true); //Switch On Relay 3
   Shelly_SwitchMode(25537, 2, false); //Switch Off Relay 3
      
   Shelly_SwitchMode(25537, 3, true); //Switch On Relay 4
   Shelly_SwitchMode(25537, 3, false); //Switch Off Relay 4
   ```