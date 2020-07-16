# ShellyDuo
   This module enables the integration of a Shelly Duo in IP-Symcon.
     
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (ShellyBulbDuo-deviceid) of the ShellyDuo is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
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
   RequestAction(56789, 40); //Dim to 40%
   ```
   Variable ID White: 34567
   ```php
   RequestAction(34567, 50); //Set to 50%
   RequestAction(34567, 40); //Set to 40%
   ```

   Variable ID ColorTemperature: 76543
   ```php
   RequestAction(76543, 2700); //Set 2700 K
   RequestAction(76543, 2900); //Set 2900 K
   ```
