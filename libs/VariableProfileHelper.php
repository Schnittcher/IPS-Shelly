<?php

/**
 * @addtogroup generic
 * @{
 *
 * @package       generic
 * @file          VariableProfileHelper.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       5.0
 */

/**
 * Trait mit Hilfsfunktionen für Variablenprofile.
 */
trait VariableProfileHelper
{
    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ bool mit Assoziationen.
     *
     * @param string $Name         Name des Profils.
     * @param string $Icon         Name des Icon.
     * @param string $Prefix       Prefix für die Darstellung.
     * @param string $Suffix       Suffix für die Darstellung.
     * @param array  $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileBooleanEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        $this->RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix);
        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
        }
    }

    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ integer mit Assoziationen.
     *
     * @param string $Name         Name des Profils.
     * @param string $Icon         Name des Icon.
     * @param string $Prefix       Prefix für die Darstellung.
     * @param string $Suffix       Suffix für die Darstellung.
     * @param array  $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations, $MaxValue = -1, $StepSize=0)
    {
        $this->RegisterProfileEx(VARIABLETYPE_INTEGER, $Name, $Icon, $Prefix, $Suffix, $Associations, $MaxValue, $StepSize);
    }

    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ float mit Assoziationen.
     *
     * @param string $Name         Name des Profils.
     * @param string $Icon         Name des Icon.
     * @param string $Prefix       Prefix für die Darstellung.
     * @param string $Suffix       Suffix für die Darstellung.
     * @param array  $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileFloatEx($Name, $Icon, $Prefix, $Suffix, $Associations, $MaxValue = -1, $StepSize=0, $Digits=0)
    {
        $this->RegisterProfileEx(VARIABLETYPE_FLOAT, $Name, $Icon, $Prefix, $Suffix, $Associations, $MaxValue, $StepSize, $Digits);
    }

    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ string mit Assoziationen.
     *
     * @param string $Name         Name des Profils.
     * @param string $Icon         Name des Icon.
     * @param string $Prefix       Prefix für die Darstellung.
     * @param string $Suffix       Suffix für die Darstellung.
     * @param array  $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileStringEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        $this->RegisterProfileEx(VARIABLETYPE_STRING, $Name, $Icon, $Prefix, $Suffix, $Associations);
    }
    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ bool.
     *
     * @param string $Name   Name des Profils.
     * @param string $Icon   Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     */
    protected function RegisterProfileBoolean($Name, $Icon, $Prefix, $Suffix)
    {
        $this->RegisterProfile(VARIABLETYPE_BOOLEAN, $Name, $Icon, $Prefix, $Suffix, 0, 0, 0);
    }

    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ integer.
     *
     * @param string $Name     Name des Profils.
     * @param string $Icon     Name des Icon.
     * @param string $Prefix   Prefix für die Darstellung.
     * @param string $Suffix   Suffix für die Darstellung.
     * @param int    $MinValue Minimaler Wert.
     * @param int    $MaxValue Maximaler wert.
     * @param int    $StepSize Schrittweite
     */
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        $this->RegisterProfile(VARIABLETYPE_INTEGER, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize);
    }

    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ float.
     *
     * @param string $Name     Name des Profils.
     * @param string $Icon     Name des Icon.
     * @param string $Prefix   Prefix für die Darstellung.
     * @param string $Suffix   Suffix für die Darstellung.
     * @param int    $MinValue Minimaler Wert.
     * @param int    $MaxValue Maximaler wert.
     * @param int    $StepSize Schrittweite
     */
    protected function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
    {
        $this->RegisterProfile(VARIABLETYPE_FLOAT, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);
    }

    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ VarType mit Assoziationen.
     *
     * @param int    $VarTyp   Typ der Variable
     * @param string $Name         Name des Profils.
     * @param string $Icon         Name des Icon.
     * @param string $Prefix       Prefix für die Darstellung.
     * @param string $Suffix       Suffix für die Darstellung.
     * @param array  $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileEx($VarTyp, $Name, $Icon, $Prefix, $Suffix, $Associations, $MaxValue = -1, $StepSize=0, $Digits=0)
    {
        if (count($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            if ($MaxValue == -1) {
                $MaxValue = $Associations[count($Associations) - 1][0];
            }
        }
        $this->RegisterProfile($VarTyp, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);
        $old = IPS_GetVariableProfile($Name)['Associations'];
        $OldValues = array_column($old, 'Value');
        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
            $OldKey = array_search($Association[0], $OldValues);
            if (!($OldKey === false)) {
                unset($OldValues[$OldKey]);
            }
        }
        foreach ($OldValues as $OldKey => $OldValue) {
            IPS_SetVariableProfileAssociation($Name, $OldValue, '', '', 0);
        }
    }
    /**
     * Erstellt und konfiguriert ein VariablenProfil für den Typ float.
     *
     * @param int    $VarTyp   Typ der Variable
     * @param string $Name     Name des Profils.
     * @param string $Icon     Name des Icon.
     * @param string $Prefix   Prefix für die Darstellung.
     * @param string $Suffix   Suffix für die Darstellung.
     * @param int    $MinValue Minimaler Wert.
     * @param int    $MaxValue Maximaler wert.
     * @param int    $StepSize Schrittweite
     */
    protected function RegisterProfile($VarTyp, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits = 0)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, $VarTyp);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != $VarTyp) {
                throw new \Exception('Variable profile type does not match for profile ' . $Name, E_USER_WARNING);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        switch ($VarTyp) {
            case VARIABLETYPE_FLOAT:
                IPS_SetVariableProfileDigits($Name, $Digits);
                // no break
            case VARIABLETYPE_INTEGER:
                IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
                break;
        }
    }

    /**
     * Löscht ein Variablenprofile, sofern es nicht außerhalb dieser Instanz noch verwendet wird.
     *
     * @param string $Name Name des zu löschenden Profils.
     */
    protected function UnregisterProfile(string $Name)
    {
        if (!IPS_VariableProfileExists($Name)) {
            return;
        }
        foreach (IPS_GetVariableList() as $VarID) {
            if (IPS_GetParent($VarID) == $this->InstanceID) {
                continue;
            }
            if (IPS_GetVariable($VarID)['VariableCustomProfile'] == $Name) {
                return;
            }
            if (IPS_GetVariable($VarID)['VariableProfile'] == $Name) {
                return;
            }
        }
        IPS_DeleteVariableProfile($Name);
    }
}

/* @} */