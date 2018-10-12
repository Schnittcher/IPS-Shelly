# IPS-Shelly
Mit diesem Modul ist es zur Zeit möglich ein Shelly 1 über MQTT zu schalten.

Benötigt wird ein MQTT Broker und das Modul IPS-KS-MQTT.

## Inhaltverzeichnis
1. [Konfiguration](#1-konfiguration)
2. [Funktionen](#2-funktionen)

## 1. Konfiguration

Feld | Beschreibung
------------ | -------------
MQTT Topic | Hier wird das Topic (shelly1-<deviceid>) des Shelly 1 eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist derHostname zu finden. Der Hostname ist die DeviceID!

## 2. Funktionen

### Shelly_SwitchMode($InstanceID, $Value)
Mit dieser Funktion ist es möglich das Gerät ein- bzw. auszuschalten.
```php
Shelly_SwitchMode(25537, true) //Einschalten;
Shelly_SwitchMode(25537, false) //Ausschalten;
```