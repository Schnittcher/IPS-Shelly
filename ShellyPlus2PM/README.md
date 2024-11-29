# ShellyPlus2PM
   Dieses Modul ermöglicht es, ein Shelly Plus 2PM in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.
    
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyplus2pm-deviceid) des Shelly Plus 2PM  eingetragen.
   Gerätetyp      | Relay oder Rolladen

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
   
   Variable ID Status = 12345
   ```php
   RequestAction(12345, 0);  //Rolladen hochfahren
   RequestAction(12345, 2); //Rolladen stoppen
   RequestAction(12345, 4); //Rolladen herunterfahren
   ```

   Variable ID Position = 56789
   ```php
   RequestAction(56789, 25);  //Rolladen aus 25% fahren!
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