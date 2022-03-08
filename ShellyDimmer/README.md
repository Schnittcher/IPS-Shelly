# ShellyDimmer
   Dieses Modul ermöglicht es, einen ShellyDimmer in IP-Symcon zu integrieren.
     
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellydimmer-deviceid) des ShellyDimmer eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist der Hostname zu finden. Der Hostname ist die DeviceID!
   
   ## 2. Funktionen

   ```php
   RequestAction($VariablenID, $Value);
   ```

   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.
   
   **Beispiel:**
   Variable ID Status: 12345
   ```php
   RequestAction(12345, true); //Einschalten
   RequestAction(12345, false); //Auschalten
   ```

   Variable ID Helligkeit: 56789
   ```php
   RequestAction(56789, 50); //auf 50% setzen
   RequestAction(56789, 40); //auf 40% setzen
   ```

   ## 3. Aktionen
   Dieses Modul kann die Aktion "Setze Helligkeit mit Übergang" benutzen, damit kann die Helligkeit mit einer Übergangszeit gesetzt werden.