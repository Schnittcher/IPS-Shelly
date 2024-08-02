# ShellyPlus1
   Dieses Modul ermöglicht es, ein Shelly Plus 1 / Shelly Plus 1PM in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
   ## Inhaltverzeichnis
- [ShellyPlus1](#shellyplus1)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyplus1-deviceid / shellyplus1pm-deviceid) des Shelly Plus 1 / Shelly Plus 1PM  eingetragen.
   Gerät      | Hier wird hinterlegt, um welches Shelly Plus 1 es sich handelt.
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**

   Variable ID Status 1 = 12345
   
   Variable ID Status 2 = 56789
   
   Variable ID Status 3 = 14725

   Variable ID Status 4 = 25836
   ```php
   RequestAction(12345, true);  //Status 1 Einschalten;
   RequestAction(12345, false); //Status 1 Ausschalten;
   
   RequestAction(56789, true);  //Status 2 Einschalten;
   RequestAction(56789, false); //Status 2 Ausschalten;
   
   RequestAction(14725, true);  //Status 3 Einschalten;
   RequestAction(14725, false); //Status 3 Ausschalten;

   RequestAction(25836, true);  //Status 4 Einschalten;
   RequestAction(25836, false); //Status 4 Ausschalten;
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