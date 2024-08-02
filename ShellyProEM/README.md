# ShellyProEM
   Dieses Modul ermöglicht es, ein Shelly Pro EM in IP-Symcon zu integrieren.\
    
   ## Inhaltverzeichnis
- [ShellyProEM](#shellyproem)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyproem50-deviceid) des Shelly Pro EM eingetragen.
   
## 2. Funktionen

   ```php
   SHELLY_ToggleAfter($InstanceID, $switch, $value, $toggle_after)
   ```
   Mit dieser Funktion kann ein Timer gestartet werden.

   **Beispiel:**

   ```php
   SHELLY_ToggleAfter(12345, 0, true, 10); //Schaltet Relay 0 für 10 Sekunden auf ein.
   SHELLY_ToggleAfter(12345, 0, false, 10); //Schaltet Relay 0 nach 10 Sekunden auf ein.
   ```