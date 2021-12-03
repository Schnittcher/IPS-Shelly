# ShellyPro4PM
   This module enables the integration of a Shelly Pro 4PM in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shelly4pro-deviceid) of the Shelly Pro 4PM is entered here.
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID State 1 = 12345
   
   Variable ID State 2 = 56789
   
   Variable ID State 3 = 14725

   Variable ID State 4 = 25836
   ```php
   RequestAction(12345 true); //Switch On State 1
   RequestAction(12345 false); //Switch Off State 1
   
   RequestAction(56789, true); //Switch On State 2
   RequestAction(56789, false); //Switch Off State 2
   
   RequestAction(14725, true); //Switch On State 3
   RequestAction(14725, false); //Switch Off State 3
      
   RequestAction(25836, true); //Switch On State 4
   RequestAction(25836, false); //Switch Off State 4
   ```