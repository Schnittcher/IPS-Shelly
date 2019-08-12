# IPS-ShellyRGBW2
   This module enables the integration of a ShellyRGBW2 in IP-Symcon.
       
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyrgbw2-deviceid) of the ShellyRGBW2 is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   Mode         | The mode that is defined by the Shelly Module is selected here. 
   
   ## 2. Functions
   
   **Shelly_SwitchMode($InstanceID, $Channel, $Value)**\
   It is possible to switch the device on or off with this function.
   For the mode Color, the $Channel is always 0!
   ```php
   Shelly_SwitchMode(25537, 0, true); //Switch On
   Shelly_SwitchMode(25537, 0, false); //Switch Off
   ```
   
   **Shelly_setDimmer($InstanceID, $Channel, $Value)**\
   It is possible to dim the device to a given percentage.
   Function is only available in the mode White!
   ```php
   Shelly_setDimmer(25537, 0, 50); //Dim to 50%
   ```
   
   **Shelly_setColor($InstanceID, $Value)**\
   It is possible to change the color with this function.
   Function is only available in the mode Color!
   ```php
   Shelly_setColor(25537,"ff0000"); //Color red
   ```
   
   **Shelly_setWhite($InstanceID, $Value)**\
   It is possible to change the value White with this function.
   Function is only available in the mode Color!
   ```php
   Shelly_setWhite(25537,50); //50% white
   ```
   
   **Shelly_setGain($InstanceID, $Value)**\
   It is possible to change the value Gain with this function.
   Function is only available in the mode Color!
   ```php
   Shelly_setGain(25537,50); //50%
   ```
   
   **Shelly_setEffect($InstanceID, $Value)**\
   It is possible to configure an effect with this function.
   Function is only available in the mode Color!
   ```php
   Shelly_setEffect(25537,4); //Effect Flash
   ```