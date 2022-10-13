[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/IPS-Shelly/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/IPS-Shelly/actions)

# IPS-Shelly
   Mit diesem Modul ist es möglich die Geräte von Shelly in IP-Symcon einzubinden.
 
   ## Inhaltverzeichnis
   1. [Voraussetzungen](#1-voraussetzungen)
   2. [Enthaltene Module](#2-enthaltene-module)
   3. [Installation](#3-installation)
   4. [Konfiguration in IP-Symcon](#4-konfiguration-in-ip-symcon)
   5. [Spenden](#5-spenden)
   6. [Lizenz](#6-lizenz)
   
## 1. Voraussetzungen

* mindestens IPS Version 5.1
* Aktiviertes MQTT Protkoll beim Shelly Gerät
* MQTT Server oder MQTT Client

### 1.1 Aktiviertes MQTT protokoll
Das MQTT Protokoll muss bei jedem Shelly aktiviert sein, damit das Gerät von IP-Symcon mit diesem Modul bedient werden kann.
Die Einrichtung wird über das Shelly Webinterface vorgenommen:

Internet & Security -> ADVANCED - DEVELOPER SETTINGS -> Enable action execution via MQTT

Unter Server wird die IP von IP-Symcon und der MQTT Port eingetragen.
Der Standard Port für MQTT ist 1883, sollte dieser in IP-Symcon geändert worden sein ist er unter I/O Instanzen -> Server Socket (MQTT Server #InstanzID) zu finden.

Sollen Username und Passwort verwendet werden müssen diese Daten in IP-Symcon unter Splitter Instanzen -> MQTT Server hinterlegt werden.
Die selben Zugangsdaten müssen über das Shelly Webinterface unter Internet & Security -> ADVANCED - DEVELOPER SETTINGS hinterlegt werden.

## 2. Enthaltene Module

* [Shelly1](Shelly1/README.md)
* [Shelly2](Shelly2/README.md)
* [Shelly3EM](Shelly3EM/README.md)
* [Shelly4Pro](Shelly4Pro/README.md)
* [ShellyAir](ShellyAir/README.md)
* [ShellyBulb](ShellyBulb/README.md)
* [ShellyButton1](ShellyButton1/README.md)
* [ShellyDimmer](ShellyDimmer/README.md)
* [ShellyConfigurator](ShellyConfigurator/README.md)
* [ShellyDuo](ShellyDuo/README.md)
* [ShellyEM](ShellyEM/README.md)
* [ShellyFlood](ShellyFlood/README.md)
* [ShellyGas](ShellyGas/README.md)
* [ShellyHT](ShellyHT/README.md)
* [Shellyi3](Shellyi3/README.md)
* [ShellyMotion](ShellyMotion/README.md)
* [ShellyMotion 2](ShellyMotion2/README.md)
* [ShellyPlug](ShellyPlug/README.md)
* [ShellyPlus1](ShellyPlus1/README.md)
* [ShellyPlus2PM](ShellyPlus2PM/README.md)
* [ShellyPlusHT](ShellyPlusHT/README.md)
* [ShellyPlusi4](ShellyPlusi4/README.md)
* [ShellyPro1](ShellyPro1/README.md)
* [ShellyPro2](ShellyPro2/README.md)
* [ShellyPro4PM](ShellyPro4PM/README.md)
* [ShellyRGBW2](ShellyRGBW2/README.md)
* [ShellySense](ShellySense/README.md)
* [ShellySmoke](ShellySmoke/README.md)
* [ShellyTRV](ShellyTRV/README.md)
* [ShellyUni](ShellyUni/README.md)
* [ShellyVintge](ShellyVintage/README.md)
* [ShellyWindow](ShellyWindow/README.md)

## 3. Installation
Installation über den IP-Symcon Module Store.

## 4. Konfiguration in IP-Symcon
Das Modul kann mit dem internen MQTT Server betrieben werden, oder aber mit einem externen MQTT Broker.
Wenn ein externer MQTT Broker verwendet werden soll, dann muss aus dem Module Store der MQTTClient installiert werden.

Standardmäßig wird der MQTT Server bei den Geräteinstanzen als Parent hinterlegt, wenn aber ein externer Broker verwendet werden soll, muss der MQTT Client per Hand angelegt werden und in der Geräteinstanz unter "Gateway ändern" ausgewählt werden.

Die weitere Dokumentation bitte den einzelnen Modulen entnehmen.

## 5. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a> <a href="https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share" target="_blank">Amazon Wunschzettel</a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
