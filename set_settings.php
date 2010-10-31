<?php
/**
 * Display/save mythepisode default settings
 *
 * @url         $URL: $
 * @date        $Date: $
 * @version     $Revision$
 * @author      $Author: $
 * @license     GPL
 *
/**/

// Copy configuration file to data/episode if it doesn't exist
$rootDir    = getcwd();
$scriptDir  = "$rootDir/modules/episode/utils";
$dataDir    = "$rootDir/data";
$epDir      = "$dataDir/episode";
$configFile = "$epDir/config.ini";

if (!file_exists($configFile))
    copy("$scriptDir/config.template", "$configFile");

$config = parse_ini_file($configFile, 1);

// These settings are limited to Mythepisode itself
$Settings_Hosts = 'Mythepisode';
