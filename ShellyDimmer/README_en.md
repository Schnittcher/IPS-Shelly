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

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.

   **Example:**
   Variable ID State: 12345
   ```php
   RequestAction(12345, true); //Switch On
   RequestAction(12345, false); //Switch On
   ```

   Variable ID Brightness: 56789
   ```php
   RequestAction(56789, 50); //Dim to 50%
   RequestAction(56789, 40); //Dim to 40%
   ```

   ## 3. Actions
   This module can use the action "set brightness with transition".
   With this action you can set the brightess with a transition time.