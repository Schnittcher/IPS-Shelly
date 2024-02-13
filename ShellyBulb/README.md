# ShellyBulb
   Dieses Modul ermöglicht es, eine Shelly Bulb RGBW Lampe in IP-Symcon zu integrieren.
     
   ## Inhaltverzeichnis
- [ShellyBulb](#shellybulb)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
  - [3. Aktionen](#3-aktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellycolorbulb-deviceid) der Shelly Bulb RGBW Lampe eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist der Hostname zu finden. Der Hostname ist die DeviceID!
      
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

   Variable ID Effekt: 12345
   ```php
   RequestAction(12345,3 ); //Effekt Blitzlicht
   ```

   Variable ID Helligkeit: 56789
   ```php
   RequestAction(56789, 50); //auf 50% setzen
   RequestAction(56789, 40); //auf 40% setzen
   ```

   Variable ID Weiß: 34567
   ```php
   RequestAction(34567, 50); //auf 50% setzen
   RequestAction(34567, 40); //auf 40% setzen
   ```

   Variable ID Farbtemperatur: 76543
   ```php
   RequestAction(76543, 2700); //auf 2700 K setzen
   RequestAction(76543, 2900); //auf 2900 K setzen
   ```

   Variable ID Farbe: 14725
   ```php
   RequestAction(14725, 0xff0000); //Farbe Rot
   ```

   Variable ID Gain: 15935
   ```php
   RequestAction(15935,50); //50% gain
   ```

   ## 3. Aktionen
   Dieses Modul kann die Aktion "Setze Helligkeit mit Übergang" benutzen, damit kann die Helligkeit mit einer Übergangszeit gesetzt werden.