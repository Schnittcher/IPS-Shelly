# IPS-Shelly4Pro
   Dieses Modul ermöglicht es, ein Shelly4Pro in IP-Symcon zu integrieren.
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   MQTT Topic | Hier wird das Topic (shellyswitch-deviceid) des Shelly4Pro eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   
   ## 2. Funktionen
   
   **Shelly_SwitchMode($InstanceID, $Relay, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_SwitchMode(25537, 0, true) //Relay 1 Einschalten;
   Shelly_SwitchMode(25537, 0, false) //Relay 1 Ausschalten;
   
   Shelly_SwitchMode(25537, 1, true) //Relay 2 Einschalten;
   Shelly_SwitchMode(25537, 1, false) //Relay 2 Ausschalten;
   
   Shelly_SwitchMode(25537, 2, true) //Relay 3 Einschalten;
   Shelly_SwitchMode(25537, 2, false) //Relay 3 Ausschalten;
      
   Shelly_SwitchMode(25537, 3, true) //Relay 4 Einschalten;
   Shelly_SwitchMode(25537, 3, false) //Relay 4 Ausschalten;
   ```