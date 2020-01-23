# ShellyDimmer
   This module enables the integration of a ShellyDimmer in IP-Symcon.
     
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellydimmer-deviceid) of the ShellyDimmer is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions
   
   **Shelly_SwitchMode($InstanceID, $Value)**\
   It is possible to switch the device on or off with this function.
   ```php
   Shelly_SwitchMode(25537, true) //Switch On;
   Shelly_SwitchMode(25537, false) //Switch Off;
   ```

   **Shelly_DimSet($InstanceID, $Value)**\
   It is possible to dim the device to a given percentage.
   ```php
   Shelly_DimSet(25537, 50); //Dim to 50%