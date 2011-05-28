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
    $showsDat   = "$epDir/shows.dat";
    $configFile = "$epDir/config.ini";

// Exit if data files don't exist
    if (!file_exists($showsDat)) {
        custom_error(' Please select TV Episodes first, and then return to configuration.');
        exit;
    }

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
        if (isset($_POST['mythtv_version']))
            $mythtv_version = $_POST['mythtv_version'];
        else
            $mythtv_version = $config['mythtvVersion'];
        $newLine = "mythtvVersion = $mythtv_version";
        replaceLine($configFile, mythtvVersion, $newLine);

        if (isset($_POST['default_page'])) 
            $default_page = $_POST['default_page'];
        else
            $default_page = $config['defaultView'];
        $newLine = "defaultView = $default_page";
        replaceLine($configFile, defaultView, $newLine);

        if (isset($_POST['display_site']))
            $default_site = $_POST['display_site'];
        else
            $default_site = $config['defaultSite'];
        $newLine = "defaultSite = $default_site";
        replaceLine($configFile, defaultSite, $newLine);

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

        if ($_POST['thumbnail_size'] != "")
            $thumbnail_size = $_POST['thumbnail_size'];
        else
            $thumbnail_size = "250";
        $newLine = "thumbnailSize = $thumbnail_size";
        replaceLine($configFile, thumbnailSize, $newLine);

        if ($_POST['country_list'] != "")
            $country_list = strtoupper($_POST['country_list']);
        else
            $country_list = "US";
        $newLine = "countryList = $country_list";
        replaceLine($configFile, countryList, $newLine);

    }

// Copy configuration file to data/episode if it doesn't exist
    if (!file_exists($configFile))
        copy("$scriptDir/config.template", "$configFile");

    $config = parse_ini_file($configFile, 1);

    $defaultView   = (empty($config['defaultView']))   ? 'recorded'   : $config['defaultView'];
    $defaultSite   = (empty($config['defaultSite']))   ? 'TVRage.com' : $config['defaultSite'];
    $matchPercent  = (empty($config['matchPercent']))  ? '85'         : $config['matchPercent'];
    $maxFileAge    = (empty($config['maxFileAge']))    ? '7'          : $config['maxFileAge'];
    $tvwishHide    = (empty($config['tvwishHide']))    ? '0'          : $config['tvwishHide'];
    $thumbnailSize = (empty($config['thumbnailSize'])) ? '250'        : $config['thumbnailSize'];
    $countryList   = (empty($config['countryList']))   ? 'US'         : $config['countryList'];
    $mythtvVersion = (empty($config['mythtvVersion'])) ? '.24+'       : $config['mythtvVersion'];

// These settings are limited to Mythepisode itself
    $Settings_Hosts = 'Mythepisode';
