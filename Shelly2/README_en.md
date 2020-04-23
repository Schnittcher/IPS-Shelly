# Shelly2
   This module enables the integration of a Shelly2 in IP-Symcon.\
   The module can be configured as relay or roller in IP-Symcon.
     
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyswitch-deviceid) of the Shelly2 is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   Device Type  | Relay or Shutter
   
   ## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   ### 2.1 Relay

   **Example:**

   Variable ID State 1  = 12345

   Variable ID State 2  = 56789
   ```php
   RequestAction(12345, true); //Switch On State 1
   RequestAction(12345, false); //Switch Off State 1

   RequestAction(56789, true); //Switch On State 1
   RequestAction(56789, false); //Switch Off State 1
   ```
   
  ### 2.2 Shutter

   **Example:**
   
   Variable ID Shutter = 12345
   ```php
   RequestAction(12345, 0);  //Move shutter up
   RequestAction(12345, 2); //Stop shutter
   RequestAction(12345, 4); ////Move shutter down

   ```

   Variable ID Position = 56789
   ```php
   RequestAction(56789, 25);  //Move shutter to 25%
   ```