<?php
/**
 * Created by PhpStorm.
 * User: Flavian Ovyn
 * Date: 2/10/2015
 * Time: 18:03
 */

/**
 * Fonction permettant la récupération du fichier de configuration
 * @return array associatif dont les clés, sont les sections
 */
function getConfigFile()
{
    return parse_ini_file("../config.ini", true);
}