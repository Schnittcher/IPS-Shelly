# ShellyPro2
   This module enables the integration of a Shelly Pro 2 in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellypro2-deviceid) of the Shelly Pro 2 is entered here.
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID State 1 = 12345
   
   Variable ID State 2 = 56789
   
   ```php
   RequestAction(12345 true); //Switch On State 1
   RequestAction(12345 false); //Switch Off State 1
   
   RequestAction(56789, true); //Switch On State 2
   RequestAction(56789, false); //Switch Off State 2
   
   ```