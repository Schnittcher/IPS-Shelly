# IPS-Shelly2
   This module enables the integration of a Shelly 2 in IP-Symcon.\
   The module can be configured as relay or roller in IP-Symcon.
     
   ## Table of Contents
   1. [Configuration](#1-configuration)
   2. [Functions](#2-functions)
   
   ## 1. Configuration
   
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyswitch-deviceid) of the Shelly 2 is entered here. Currently, the following URL needs to be called for this: http://ShellyIP/settings The host name is found there. The host name is the DeviceID!
   Device Type  | Relay or Shutter
   
   ## 2. Functions
   
   ### 2.1 Relay
   
   **Shelly_SwitchMode($InstanceID, $Relay, $Value)**\
   It is possible to switch the device on or off with this function.
   ```php
   Shelly_SwitchMode(25537, 0, true); //Switch On Relay 1
   Shelly_SwitchMode(25537, 0, false); //Switch Off Relay 1
   
   Shelly_SwitchMode(25537, 1, true); //Switch On Relay 2
   Shelly_SwitchMode(25537, 1, false); //Switch Off Relay 2
   ```
   
  ### 2.2 Shutter
  
  **Shelly_MoveDown($InstanceID)**\
   It is possible to move the roller down with this function.
  ```php
  Shelly_MoveDown(25537); //Move shutter down
  ```
  
  **Shelly_MoveUp($InstanceID)**\
   It is possible to move the roller up with this function.
  ```php
  Shelly_MoveUp(25537); //Move shutter up
  ```
  **Shelly_Move($InstanceID, $Position)**\
   It is possible to move the roller to a specific position with this function.
  ```php
  Shelly_Move(25537,25); //Move shutter to 25%
  ```
  
  **Shelly_Stop($InstanceID)**\
   It is possible to stop the roller with this function.
  ```php
  Shelly_Stop(25537); //Stop shutter
  ```