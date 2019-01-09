# IPS-Shelly4Pro
   Mit diesem Modul ist es zur Zeit möglich ein Shelly 2 über MQTT zu schalten.
   
   Benötigt wird ein MQTT Broker und das Modul IPS-KS-MQTT.
   
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   MQTT Topic | Hier wird das Topic (shellyswitch-deviceid) des Shelly 1 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   Device Type | Relay oder Roller
   
   ## 2. Funktionen
   
   ### Shelly_SwitchMode($InstanceID, $Relay, $Value)
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_SwitchMode(25537, 0, true) //Relay 1 Einschalten;
   Shelly_SwitchMode(25537, 0, false) //Relay 1 Ausschalten;
   
   Shelly_SwitchMode(25537, 1, true) //Relay 2 Einschalten;
   Shelly_SwitchMode(25537, 1, false) //Relay 1 Ausschalten;
   ```