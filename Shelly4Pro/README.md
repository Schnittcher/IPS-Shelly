# Shelly4Pro
   Dieses Modul ermöglicht es, ein Shelly4Pro in IP-Symcon zu integrieren.\
   Es können die Kanäle geschaltet werden und die Messwerte werden in IP-Symcon dargestellt.   
    
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | ----------------
   MQTT Topic | Hier wird das Topic (shelly4pro-deviceid) des Shelly4Pro eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   
   ## 2. Funktionen
   
   ```php
   RequestAction($VariablenID, $Value);
   ```
   Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

   **Beispiel:**

   Variable ID Relay 1 = 12345
   
   Variable ID Relay 2 = 56789
   
   Variable ID Relay 3 = 14725

   Variable ID Relay 4 = 25836
   ```php
   RequestAction(12345, true);  //Relay 1 Einschalten;
   RequestAction(12345, false); //Relay 1 Ausschalten;
   
   RequestAction(56789, true);  //Relay 2 Einschalten;
   RequestAction(56789, false); //Relay 2 Ausschalten;
   
   RequestAction(14725, true);  //Relay 3 Einschalten;
   RequestAction(14725, false); //Relay 3 Ausschalten;

   RequestAction(25836, true);  //Relay 4 Einschalten;
   RequestAction(25836, false); //Relay 4 Ausschalten;
   ```