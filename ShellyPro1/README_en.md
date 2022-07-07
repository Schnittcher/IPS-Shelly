# ShellyPro1PM
   This module enables the integration of a Shelly Pro 1 / Shelly Pro 1PM in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellypro1-deviceid / shellypro1pm-deviceid) of the Shelly Pro 1 / Shelly Pro 1PM is entered here.
   Device       | The Type of the Shelly Pro 1.
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID State = 12345
   ```php
   RequestAction(12345 true); //Switch On State
   RequestAction(12345 false); //Switch Off State
   ```