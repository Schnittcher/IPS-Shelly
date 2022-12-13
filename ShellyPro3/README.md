# ShellyPro3
   Dieses Modul ermöglicht es, ein Shelly Pro 3 in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
   ## Inhaltverzeichnis
- [ShellyPro3](#shellypro3)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shelly3pro-deviceid) des Shelly Pro 3 eingetragen.
   
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**

   Variable ID Status 1 = 12345
   
   Variable ID Status 2 = 56789
   
   Variable ID Status 3 = 14725

   ```php
   RequestAction(12345, true);  //Status 1 Einschalten;
   RequestAction(12345, false); //Status 1 Ausschalten;
   
   RequestAction(56789, true);  //Status 2 Einschalten;
   RequestAction(56789, false); //Status 2 Ausschalten;
   
   RequestAction(14725, true);  //Status 3 Einschalten;
   RequestAction(14725, false); //Status 3 Ausschalten;
   ```