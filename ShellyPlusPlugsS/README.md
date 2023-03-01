# ShellyPlusPlugS
   Dieses Modul ermöglicht es, ein Shelly Plus Plug S in IP-Symcon zu integrieren.\
       
## Inhaltverzeichnis
- [ShellyPlusPlugS](#shellyplusplugs)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
## 1. Konfiguration  
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shellyplusplugs-deviceid) des Shelly Plus Plug S eingetragen.
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