# ShellyPro1
   Dieses Modul ermöglicht es, ein Shelly Pro 1 / Shelly Pro 1PM in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellypro1-deviceid / shellypro1pm-deviceid) des Shelly Pro 1 / Shelly Pro 1PM eingetragen.
   Gerät      | Hier wird hinterlegt, um welches Shelly Pro 1 es sich handelt.
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**

   Variable ID Status = 12345
   ```php
   RequestAction(12345, true);  //Status Einschalten;
   RequestAction(12345, false); //Status Ausschalten;
   ```