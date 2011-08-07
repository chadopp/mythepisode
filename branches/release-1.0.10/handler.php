<?php
/**
 * handler file
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Update include path to include modules/tv classes/includes
    ini_set('include_path', ini_get('include_path').':modules/tv');

// Setup some paths
    $rootDir       = getcwd();
    $scriptDir     = "$rootDir/modules/episode/utils";
    $dataDir       = "$rootDir/data";
    $epDir         = "$dataDir/episode";
    $showDir       = "$epDir/shows";
    $imageDir      = "$epDir/images";
    $cacheDir      = "$epDir/cache";
    $wishDir       = "$epDir/tvwish";
    $configFile    = "$epDir/config.ini";
    $showsOverride = "$epDir/override.txt";

// Create the episode dir if it doesn't exist
    if (!is_dir($epDir) && !mkdir($epDir, 0775)) {
        custom_error('Error creating '.$epDir.': Please check permissions on the data directory.');
        exit;
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

// Load a custom page
    switch ($Path[1]) {
        case 'show';
            require_once 'modules/episode/show.php';
            exit;
        case 'episodes';
            require_once 'modules/episode/episodes.php';
            exit;
        case 'tvwish_list';
            require_once 'modules/episode/tvwish_list.php';
            exit;
        case 'previous_recordings';
            require_once 'modules/episode/previous_recordings.php';
            exit;
        default;
            require_once 'modules/episode/show.php';
            exit;
    }
