# IPS-ShellyPlug
   Dieses Modul ermöglicht es, ein ShellyPlug in IP-Symcon zu integrieren.\
   Es kann das Relay geschaltet werden und Messwerte werden in IP-Symcon dargestellt.
      
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   MQTT Topic | Hier wird das Topic (shellyswitch-deviceid) des Shelly Plug eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   
   ## 2. Funktionen
   
   ### Shelly_SwitchMode($InstanceID, $Relay, $Value)
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_SwitchMode(25537, 0, true) //Relay Einschalten;
   Shelly_SwitchMode(25537, 0, false) //Relay Ausschalten;
   ```