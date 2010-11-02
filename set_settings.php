<?php
/**
 * Display/save mythepisode default settings
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Set some directory paths
$rootDir    = getcwd();
$scriptDir  = "$rootDir/modules/episode/utils";
$dataDir    = "$rootDir/data";
$epDir      = "$dataDir/episode";
$configFile = "$epDir/config.ini";

// Function for replacing line in text file.
function replaceLine($configFile, $pattern, $replacement) {
    if(!file_exists($configFile)) {
        print "The specified file doesn't seem to exist.";
    } else {
        $f = file($configFile);
        $content;
        for($i = 0; $i < count($f); $i++) {
            if(eregi($pattern, $f[$i])) {
                $content .= $replacement . "\n";
                $match = 1;
            } else {
                $content .= $f[$i];
            }
        }
        // If the variable doesn't exist in config.ini we add it.
        if (!$match) {
            $replacement = "\n; See wiki for use\n" . $replacement;
            $content .= $replacement . "\n";
        }
        $fi = fopen($configFile, "w");
        fwrite($fi, $content);
        fclose($fi);
    }
}

// Save configuration changes
if ($_POST['save']) {
    if (isset($_POST['default_page'])) 
        $default_page = $_POST['default_page'];
    else
        $default_page = $config['defaultView'];
    $newLine = "defaultView = $default_page";
    replaceLine($configFile, defaultView, $newLine);

    if (isset($_POST['display_tvwish'])) 
        $display_tvwish = $_POST['display_tvwish'];
    else
        $display_tvwish = $config['tvwishHide'];
    if ($display_tvwish == 'yes')
        $display_tvwish = 0;
    else 
        $display_tvwish = 1;
    $newLine = "tvwishHide = $display_tvwish";
    replaceLine($configFile, tvwishHide, $newLine);

    if (isset($_POST['episode_match'])) 
        $episode_match = $_POST['episode_match'];
    else
        $episode_match = $config['matchPercent'];
    $newLine = "matchPercent = $episode_match";
    replaceLine($configFile, matchPercent, $newLine);

    if (isset($_POST['episode_update']))
        $episode_update = $_POST['episode_update'];
    else
        $episode_update = $config['maxFileAge'];
    $newLine = "maxFileAge = $episode_update";
    replaceLine($configFile, maxFileAge, $newLine);

    if (isset($_POST['thumbnail_size']))
        $thumbnail_size = $_POST['thumbnail_size'];
    else
        $thumbnail_size = $config['thumbnailSize'];
    $newLine = "thumbnailSize = $thumbnail_size";
    replaceLine($configFile, thumbnailSize, $newLine);

    if (isset($_POST['country_list']))
        $country_list = strtoupper($_POST['country_list']);
    else
        $country_list = strtoupper($config['countryList']);
    $newLine = "countryList = $country_list";
    replaceLine($configFile, countryList, $newLine);
}

// Copy configuration file to data/episode if it doesn't exist
if (!file_exists($configFile))
    copy("$scriptDir/config.template", "$configFile");

$config = parse_ini_file($configFile, 1);

$thumbnailSize = (empty($config['thumbnailSize'])) ? '170' : $config['thumbnailSize'];
$countryList   = (empty($config['countryList'])) ? 'US' : $config['countryList'];

// These settings are limited to Mythepisode itself
$Settings_Hosts = 'Mythepisode';
