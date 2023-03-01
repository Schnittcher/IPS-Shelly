# ShellyPlusPlugS
   This module enables the integration of a Shelly Plus Plug SD in IP-Symcon.\
       
## Table of Contents
- [ShellyPlusPlugS](#shellyplusplugs)
  - [Table of Contents](#table-of-contents)
  - [1. Configuration](#1-configuration)
  - [2. Functions](#2-functions)
   
## 1. Configuration
  
   Field        | Description
   ------------ | -------------
   MQTT Topic   | The Topic (shellyplusplugs-deviceid) of the Shelly Plus Plug S is entered here.
   
## 2. Functions

   ```php
   RequestAction($VariablenID, $Value);
   ```
   It´s possible to use all variable actions with this function.
   
   **Example:**

   Variable ID State = 12345
   
   ```php
   RequestAction(12345 true); //Switch On 
   RequestAction(12345 false); //Switch Off
   ```