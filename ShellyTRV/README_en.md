# ShellyTRV
   This module enables the integration of a ShellyTRV in IP-Symcon.
     
   ## Table of Contents
- [ShellyTRV](#shellytrv)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellytrv-deviceid) of the ShellyTRV is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   
   ## 2. Functions
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   **Example:**
   
   Variable ID Schedule: 12345
   ```php
   RequestAction(12345, true); //Switch schedule on 
   RequestAction(12345, false); //Switch schedule off
   ```

   Variable ID target Temperature: 12345
   ```php
   RequestAction(12345, 20); //Set target temperature to 20 degrees
   ```

   Variable ID Schedule Profile: 12345
   ```php
   RequestAction(12345, 5); //Set schedule profile to 5
   ```