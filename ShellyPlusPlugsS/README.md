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
   
   **RequestAction($VariablenID, $Value);**
   
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**

   Variable ID Status = 12345
   ```php
   RequestAction(12345, true);  //Status Einschalten;
   RequestAction(12345, false); //Status Ausschalten;
   ```

   **SHELLY_SetLEDColorSwitchState($InstanzID, string $state, array $rgb, int $brightness);**

   Mit dieser Funktion kann der LED Ring auf den Modus "Switch state" umgestellt werden, zeitgleich wird die Farbe für den Status "on" oder "off" gesetzt.
   Der RGB Farbraum ist von 0-100.
   
   **Beispiel:**

   Instanz ID = 12345
   ```php
   SHELLY_SetLEDColorSwitchState(12345, 'on', [0 => 0, 1 => 100, 2=> 0], 100);  //Schaltet den Mode auf "Switch state" und die Farbe für den Status "on" auf Grün.
   ```

   **SHELLY_SetLEDPowerConsumption($InstanzID,int $brightness);**
   
   Mit dieser Funktion kann der LED Ring auf den Modus "Power consumption" umgestellt werden, zeitgleich wird die Helligkeit für den die Farbe gesetzt.
   Der Wert für Helligkeit ist von 0-100.
   
   **Beispiel:**

   Instanz ID = 12345
   ```php
   SHELLY_SetLEDPowerConsumption(12345, 100);  //Schaltet den Mode auf "Power consumption" und die Helligkeit auf 100%.
   ```

   **SHELLY_SetLEDOff($InstanzID);**

   Mit dieser Funktion kann der LED Ring auf den Modus "Completely off" umgestellt werden.

   **Beispiel:**

   Instanz ID = 12345
   ```php
   SHELLY_SetLEDOff(12345);  //Schaltet den Mode auf "Completely off".
   ```