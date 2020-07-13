# ShellySmoke
   Dieses Modul integriert den Shelly Gas in IP-Symcon.\
   Die Messwerte und Alarmmeldungen von einem Shelly Gas werden in IP-Symcon übertragen.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellygas-deviceid) des Shelly Gas eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist der Hostname zu finden. Der Hostname ist die DeviceID!
   
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**
   
   Variable ID Steuerung: 12345
   ```php
   RequestAction(12345, 0); //Self Test
   RequestAction(12345, 1); //Mute
   RequestAction(12345, 2); //Unmute
   ```