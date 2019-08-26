[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-5.1%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://styleci.io/repos/152767193/shield?style=flat)](https://styleci.io/repos/152767193)

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

### 1.1 Aktiviertes MQTT protokoll
Das MQTT Protokoll muss bei jedem Shelly aktiviert sein, damit das Gerät von IP-Symcon mit diesem Modul bedient werden kann.
Die Einrichtung wird über das Shelly Webinterface vorgenommen:

Internet & Security -> ADVANCED - DEVELOPER SETTINGS -> Enable action execution via MQTT

Unter Server wird die IP von IP-Symcon und der MQTT Port eingetragen.
Der Standard Port für MQTT ist 1883, sollte dieser in IP-Symcon geändert worden sein ist er unter I/O Instanzen -> Server Socket (MQTT Server #InstanzID) zu finden.

Sollen Username und Passwort verwendet werden müssen diese Daten in IP-Symcon unter Splitter Instanzen -> MQTT Server hinterlegt werden.
Die selben Zugangsdaten müssen über das Shelly Webinterface unter Internet & Security -> ADVANCED - DEVELOPER SETTINGS hinterlegt werden.

## 2. Enthaltene Module

* [IPS-Shelly1](IPS-Shelly1/README.md)
* [IPS-Shelly2](IPS-Shelly2/README.md)
* [IPS-Shelly4Pro](IPS-Shelly4Pro/README.md)
* [IPS-ShellyHT](IPS-ShellyHT/README.md)
* [IPS-ShellyPlug](IPS-ShellyPlug/README.md)
* [IPS-ShellyRGBW2](IPS-ShellyRGBW2/README.md)
* [IPS-ShellySense](IPS-ShellySense/README.md)
* [IPS-ShellySmoke](IPS-ShellySmoke/README.md)
* [IPS-ShellyEM](IPS-ShellyEM/README.md)
* [IPS-ShellyFlood](IPS-ShellyFlood/README.md)

## 3. Installation
Installation über den IP-Symcon Module Store.

## 4. Konfiguration in IP-Symcon
Bitte den einzelnen Modulen entnehmen.

## 5. Spenden

Dieses Modul ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)