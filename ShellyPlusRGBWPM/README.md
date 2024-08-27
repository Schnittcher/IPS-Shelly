# ShellyPlusRGBWPM
   Dieses Modul ermöglicht es, ein Shelly Plus RGBW PM in IP-Symcon zu integrieren.\   
    
   ## Inhaltverzeichnis
- [ShellyPlusRGBWPM](#shellyplusrgbwpm)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Konfiguration](#1-konfiguration)
  - [2. Funktionen](#2-funktionen)
   
## 1. Konfiguration

Feld | Beschreibung
------------ | ----------------
MQTT Topic | Hier wird das Topic (shellyplusrgbwpm-deviceid) des Shelly Plus PM Mini eingetragen.

## 2. Funktionen

```php
RequestAction($VariablenID, $Value);
```

Mit dieser Funktion können alle Aktionen einer Variable ausgelöst werden.

**Beispiel:**
Variable ID Status: 12345
```php
RequestAction(12345, true); //Einschalten
RequestAction(12345, false); //Auschalten
```

Variable ID Helligkeit: 56789
```php
RequestAction(56789, 50); //auf 50% setzen
RequestAction(56789, 40); //auf 40% setzen
```

Variable ID Weiß: 34567
```php
RequestAction(34567, 50); //auf 50% setzen
RequestAction(34567, 40); //auf 40% setzen
```

Variable ID Farbe: 14725
```php
RequestAction(14725, 0xff0000); //Farbe Rot
```

```php
SHELLY_SetLightState(int $InstanzID, int $id, bool $value, int $transition = 0, int $toggle_after = 0);
``` 
Mit dieser Funktion kann der Status der 4 Licht Kanäle gesetzt werden, wenn das Gerät auf "Lights x 4" eingestellt ist.


```php
SHELLY_SetLightBrightness(int $InstanzID, int $id, int $brightness, int $transition = 0, int $toggle_after = 0);
```
Mit dieser Funktion kann die Helligkeit der 4 Licht Kanäle gesetzt werden, wenn das Gerät auf "Lights x 4" eingestellt ist.

```php
SHELLY_SetRGBState(int $InstanzID, int $id, bool $state, int $transition =0, $toggle_after = 0);
```
Mit dieser Funktion kann der Status gesetzt werden, wenn das Gerät auf "RGB" eingestellt ist.

```php
SHELLY_SetRGBBrightness(int $InstanzID, int $id, bool $brightness, int $transition =0, $toggle_after = 0);
```
Mit dieser Funktion kann die Helligkeit gesetzt werden, wenn das Gerät auf "RGB" eingestellt ist.

```php
SHELLY_SetRGB(int $InstanzID, int $id, int $brightness, $rgb, int $transition =0, $toggle_after = 0);
```
Mit dieser Funktion kann die Helligkeit und die Farbe gesetzt werden, wenn das Gerät auf "RGB" eingestellt ist.

```php
SHELLY_SetRGBW(SetRGBWState(int $InstanzID, int $id, bool $state, int $transition =0, $toggle_after = 0));
```
Mit dieser Funktion kann der Status gesetzt werden, wenn das Gerät auf "RGBW" eingestellt ist.

```php
SHELLY_SetRGBWBrightness(int $InstanzID, int $id, bool $brightness, int $transition =0, $toggle_after = 0);
```
Mit dieser Funktion kann die Helligkeit gesetzt werden, wenn das Gerät auf "RGBW" eingestellt ist.

```php
SHELLY_SetRGBWWhite(int $InstanzID, int $id, int $white, int $transition =0, $toggle_after = 0);
```
Mit dieser Funktion kann der Weiß Wert gesetzt werden, wenn das Gerät auf "RGBW" eingestellt ist.

```php
SHELLY_SetRGBW(int $InstanzID, int $id, int $brightness, $rgb, int $white, int $transition =0, $toggle_after = 0)
```
Mit dieser Funktion kann die Helligkeit, die Farbe und der Weiß Wert gesetzt werden, wenn das Gerät auf "RGBW" eingestellt ist.