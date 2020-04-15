# ShellyVintage
   Dieses Modul ermöglicht es, eine Shelly Vintage Lampe in IP-Symcon zu integrieren.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (ShellyVintage-deviceid) der ShellyVintage Lampe eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist der Hostname zu finden. Der Hostname ist die DeviceID!
   
   ## 2. Funktionen
   
   **Shelly_DimmerSwitchMode($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_DimmerSwitchMode(25537, true); //Einschalten
   Shelly_DimmerSwitchMode(25537, false); //Ausschalten
   ```

   **Shelly_DimSet($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   ```php
   Shelly_DimSet(25537, 50); //auf 50% setzen
   Shelly_DimSet(25537, 40); //auf 40% setzen
   ```