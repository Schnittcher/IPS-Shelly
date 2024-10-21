# Gen3Shelly2PM
   Dieses Modul ermöglicht es, die Gen 3 Shelly 2 PM in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
## Inhaltverzeichnis
- [Gen3Shelly2PM](#gen3shelly2pm)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
    - [2.1 Relay](#21-relay)
    - [2.2 Rolladen](#22-rolladen)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
MQTT Topic | Hier wird das Topic Shelly 2PM  eingetragen.
Gerät      | Hier wird hinterlegt, um welches Shelly es sich handelt.

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
   RequestAction(56789, 25);  //Rolladen auf 25% fahren!
   ```

   Variable ID Position = 56788
   ```php
   RequestAction(56788, 25);  //Lamellen auf 25% fahren!
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