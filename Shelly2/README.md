# Shelly2
   Dieses Modul ermöglicht es, ein Shelly2 in IP-Symcon zu integrieren.\
   Das Modul kann in IP-Symcon als Relay oder als Rollo eingerichtet werden.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyswitch-deviceid) des Shelly2 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   Device Type | Relay oder Rolladen
   
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```

   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.
  
   ### 2.1 Relay

   **Beispiel:**

   Variable ID Status 1  = 12345

   Variable ID Status 2  = 56789
   ```php
   RequestAction(12345, true); //Einschalten Status 1
   RequestAction(12345, false); //Ausschalten Status 1

   RequestAction(56789, true); //Einschalten Status 2
   RequestAction(56789, false); //Ausschalten Status 2
   ```
   
  ### 2.2 Rolladen

   **Beispiel:**
   
   Variable ID Roller = 12345
   ```php
   RequestAction(12345, 0);  //Rolladen hochfahren
   RequestAction(12345, 2); //Rolladen stoppen
   RequestAction(12345, 4); //Rolladen herunterfahren
   ```

   Variable ID Position = 56789
   ```php
   RequestAction(56789, 25);  //Rolladen aus 25% fahren!
   ```