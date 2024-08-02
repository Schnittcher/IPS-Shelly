# ShellyPro2
   Dieses Modul ermöglicht es, ein Shelly Pro 2 in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
   ## Inhaltverzeichnis
- [ShellyPro2](#shellypro2)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellypro2-deviceid) des Shelly Pro 2 eingetragen.
   
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**

   Variable ID Status 1 = 12345
   
   Variable ID Status 2 = 56789
   
   ```php
   RequestAction(12345, true);  //Status 1 Einschalten;
   RequestAction(12345, false); //Status 1 Ausschalten;
   
   RequestAction(56789, true);  //Status 2 Einschalten;
   RequestAction(56789, false); //Status 2 Ausschalten;
   ```
   
   ```php
   SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
   ```
   Mit dieser Funktion kann ein Timer gestartet werden.

   **Beispiel:**

   ```php
   SHELLY_ToggleAfter(12345, 0, true, 10); //Schaltet Relay 0 für 10 Sekunden auf ein.
   SHELLY_ToggleAfter(12345, 0, false, 10); //Schaltet Relay 0 nach 10 Sekunden auf ein.
   ```