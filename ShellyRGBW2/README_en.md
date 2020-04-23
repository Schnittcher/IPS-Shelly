# ShellyRGBW2
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
 
   Variable ID Brightness: 56789
   ```php
   RequestAction(56789, 50); //Dim to 50%
   ```

   Variable ID Color: 14725
   ```php
   RequestAction(14725, 0xff0000); //Color red
   ```
   
   Variable ID White: 58369
   ```php
   RequestAction(58369,50); //50% white
   ```

   Variable ID Gain: 15935
   ```php
   RequestAction(15935,50); //50% gain
   ```

   Variable ID Effect: 35795
   ```php
   RequestAction(35795,4); //Effect Breath
   ```