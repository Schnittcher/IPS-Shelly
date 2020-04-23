# Shelly4Pro
   This module enables the integration of a Shelly4Pro in IP-Symcon.\
   The channels can be switched and the sensor data is visualized in IP-Symcon.   
    
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shelly4pro-deviceid) of the Shelly4Pro is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID Relay 1 = 12345
   
   Variable ID Relay 2 = 56789
   
   Variable ID Relay 3 = 14725

   Variable ID Relay 4 = 25836
   ```php
   RequestAction(12345 true); //Switch On Relay 1
   RequestAction(12345 false); //Switch Off Relay 1
   
   RequestAction(56789, true); //Switch On Relay 2
   RequestAction(56789, false); //Switch Off Relay 2
   
   RequestAction(14725, true); //Switch On Relay 3
   RequestAction(14725, false); //Switch Off Relay 3
      
   RequestAction(25836, true); //Switch On Relay 4
   RequestAction(25836, false); //Switch Off Relay 4
   ```