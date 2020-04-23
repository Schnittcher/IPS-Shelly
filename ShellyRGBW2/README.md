# ShellyRGBW2
   Dieses Modul ermöglicht es, ein Shelly RGBW2 in IP-Symcon zu integrieren.
       
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyrgbw2-deviceid) des Shelly RGBW2 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   Modus | Hier wird der Modus ausgewählt, der im Shelly Modul hinterlegt ist. 
   
   ## 2. Funktionen

   ```php
   RequestAction($VariablenID, $Value);
   ```

   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.
   
   **Beispiel:**
   
   Variable ID Status: 12345
   ```php
   RequestAction(12345, true); //Einschalten
   RequestAction(12345, false); //Ausschalten
   ```
 
   Variable ID Helligkeit: 56789
   ```php
   RequestAction(56789, 50); //Auf 50% dimmen
   ```

   Variable ID Farbe: 14725
   ```php
   RequestAction(14725, 0xff0000); //Farbe Rot
   ```
   
   Variable ID Weiß: 58369
   ```php
   RequestAction(58369,50); //50% weiß
   ```

   Variable ID Gain: 15935
   ```php
   RequestAction(15935,50); //50% gain
   ```

   Variable ID Effekt: 35795
   ```php
   RequestAction(35795,4); //Effekt Breath
   ```