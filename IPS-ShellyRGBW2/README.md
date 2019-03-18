# IPS-ShellyRGBW2
   Dieses Modul ermöglicht es, ein ShellyRGBW2 in IP-Symcon zu integrieren.
       
   ## Inhaltverzeichnis
   1. [Konfiguration](#1-konfiguration)
   2. [Funktionen](#2-funktionen)
   
   ## 1. Konfiguration
   
   Feld | Beschreibung
   ------------ | -------------
   MQTT Topic | Hier wird das Topic (shellyrgbw2-deviceid) des ShellyRGBW2 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!
   Modus | Hier wird der Modus ausgewählt, der im Shelly Modul hinterlegt ist. 
   
   ## 2. Funktionen
   
   **Shelly_SwitchMode($InstanceID, $Channel, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   $Channel ist bei dem Modus Farbe immer 0!
   ```php
   Shelly_SwitchMode(25537, 0, true) //Einschalten;
   Shelly_SwitchMode(25537, 0, false) //Ausschalten;
   ```
   
   **Shelly_SwitchDimmer($InstanceID, $Channel, $Value)**\
   Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
   Funktion nur im Modus White verfügbar!
   ```php
   Shelly_setDimmer(25537, 0, 50) //Auf 50% dimmen;
   ```
   
   **Shelly_setColor($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich die Farbe zu ändern.
   Funktion nur im Modus Farbe verfügbar!
   ```php
   Shelly_setColor(25537,"ff0000") //Farbe Rot;
   ```
   
   **Shelly_setWhite($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich den Wert Weiß zu ändern.
   Funktion nur im Modus Farbe verfügbar!
   ```php
   Shelly_setWhite(25537,50) //50% weiß
   ```
   
   **Shelly_Gain($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich den Wert Gain zu ändern.
   Funktion nur im Modus Farbe verfügbar!
   ```php
   Shelly_setColor(25537,50) //50%;
   ```
   
   **Shelly_Effect($InstanceID, $Value)**\
   Mit dieser Funktion ist es möglich einen Effekt einzustellen
   Funktion nur im Modus Farbe verfügbar!
   ```php
   Shelly_setEffect(25537,4) //Effekt Flash
   ```