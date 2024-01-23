# Gen3Shelly1Mini
   Dieses Modul ermöglicht es, die Gen 3 Shelly 1 Mini, Shelly 1 PM Mini und Shelly PM Mini in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
## Inhaltverzeichnis
- [Gen3Shelly1Mini](#gen3shelly1mini)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
MQTT Topic | Hier wird das Topic (shellyplus1-deviceid / shellyplus1pm-deviceid) des Shelly Plus 1 / Shelly Plus 1PM  eingetragen.
Gerät      | Hier wird hinterlegt, um welches Shelly es sich handelt.
## 2. Funktionen

```php
RequestAction($VariablenID, $Value);
```
Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

**Beispiel:**

Variable ID Status 1 = 12345

```php
RequestAction(12345, true);  //Status 1 Einschalten;
RequestAction(12345, false); //Status 1 Ausschalten;
```