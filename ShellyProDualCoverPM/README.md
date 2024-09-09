# ShellyProDualCoverPM
   Dieses Modul ermöglicht es, ein Shelly Pro Dual Cover PM in IP-Symcon zu integrieren.
     
## Inhaltverzeichnis
- [ShellyProDualCoverPM](#shellyprodualcoverpm)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)

## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
MQTT Topic | Hier wird das Topic (shellyprodualcoverpm-deviceid) des Shelly Pro Dual Cover PM eingetragen. Dazu muss zur Zeit die folgende URL aufgerufen werden: http://ShellyIP/settings dort ist der Hostname zu finden. Der Hostname ist die DeviceID!

## 2. Funktionen
**Beispiel:**

Variable ID Roller = 12345
```php
RequestAction(12345, 0); //Rolladen hochfahren
RequestAction(12345, 2); //Rolladen stoppen
RequestAction(12345, 4); //Rolladen herunterfahren
```

Variable ID Position = 56789
```php
RequestAction(56789, 25); //Rolladen aus 25% fahren!
```