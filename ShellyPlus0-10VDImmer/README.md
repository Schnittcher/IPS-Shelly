# ShellyPlus0-10VDImmer
   Dieses Modul ermöglicht es, einen Shelly Plus 0-10V Dimmer in IP-Symcon zu integrieren.
     
   ## Inhaltverzeichnis
- [ShellyPlus0-10VDImmer](#shellyplus0-10vdimmer)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyplus010v-deviceid) des Shelly Plus 0-10V Dimmer eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist der Hostname zu finden. Der Hostname ist die DeviceID!
   
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

   ```php
   SHELLY_SetBrightness(int $InstanceID, int $id, int $brightness, int $transition = 0, int $toggle_after = 0);
   ```
   Instanz ID: 54321
   ```php
   SHELLY_SetBrightness(54321, 0, 50, 10, 15); //Setze Helligkeit auf 50% mit einer Übergangszeit von 10 Sekunden und einem "flip-back timer" von 15 Sekunden
   ```