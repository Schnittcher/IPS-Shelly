# IPS-Shelly2
   Dieses Modul ermöglicht es, ein Shelly2 in IP-Symcon zu integrieren.\
   Das Modul kann in IP-Symcon als Relay oder als Rollo eingerichtet werden.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   MQTT Topic | Hier wird das Topic (shellyswitch-deviceid) des Shelly 2 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   Device Type | Relay oder Roller
   
   ## 2. Funktionen
   
   ### 2.1 Relay
   
   **Shelly_SwitchMode($InstanceID, $Relay, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_SwitchMode(25537, 0, true) //Relay 1 Einschalten;
   Shelly_SwitchMode(25537, 0, false) //Relay 1 Ausschalten;
   
   Shelly_SwitchMode(25537, 1, true) //Relay 2 Einschalten;
   Shelly_SwitchMode(25537, 1, false) //Relay 2 Ausschalten;
   ```
   
  ### 2.2 Roller
  
  **Shelly_MoveDown($InstanceID)**\
  Mit dieser Funktion ist es möglich den Rolladen herunterzufahren!
  ```php
  Shelly_MoveDown(25537) //Rolladen herunterfahren;
  ```
  
  **Shelly_MoveUP($InstanceID)**\
  Mit dieser Funktion ist es möglich den Rolladen hochzufahren!
  ```php
  Shelly_MoveUP(25537) //Rolladen hochfahren;
  ```
  **Shelly_Move($InstanceID, $Position)**\
  Mit dieser Funktion ist es möglich den Rolladen auf eine bestimmte Position zu fahren!
  ```php
  Shelly_Move(25537,25) //Rolladen auf 25% fahren!
  ```
  
  **Shelly_Stop($InstanceID)**\
  Mit dieser Funktion ist es möglich den Rolladen zu stoppen!
  ```php
  Shelly_Stop(25537) //Rolladen stoppen;
  ```