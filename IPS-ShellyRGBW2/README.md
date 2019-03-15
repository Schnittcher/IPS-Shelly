# IPS-ShellyRGBW2
   Dieses Modul ermöglicht es, ein ShellyRGBW2 in IP-Symcon zu integrieren.
       
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   MQTT Topic | Hier wird das Topic (shellyrgbw2-deviceid) des ShellyRGBW2 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   Modus | Hier wird der Modus ausgewählt, der im Shelly Modul hinterlegt ist. 
   
   ## 2. Funktionen
   
   **Shelly_SwitchMode($InstanceID, $Channel, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_SwitchMode(25537, 0, true) //Einschalten;
   Shelly_SwitchMode(25537, 0, false) //Ausschalten;
   ```
   
   **Shelly_SwitchMode($InstanceID, $Channel, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_setDimmer(25537, 0, 50) //Auf 50% dimmen;
   ```