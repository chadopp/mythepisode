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

require_once "includes/init.php";
require_once "includes/sorting.php";

// Setup some paths
$rootDir   = getcwd();
$scriptDir = "$rootDir/modules/episode/utils";
$dataDir   = "$rootDir/data";
$epDir     = "$dataDir/episode";
$showDir   = "$epDir/shows";
$imageDir  = "$epDir/images";
$wishDir   = "$epDir/tvwish";

// Files/Directories used for show.php
$showsTxt      = "$epDir/shows.txt";
$showsDat      = "$epDir/shows.dat";
$showsCountry  = "$epDir/country.txt";
$showsOverride = "$epDir/override.txt";

// If set to 0 the mainpage loads slightly faster but doesn't display
// recorded shows in green.  1 should be ok in most cases 
$getrecorded   = 1;

// The default view displayed when you load the mainpage. 
// all      - display all TV shows ever aired
// current  - display TV shows that are currently being aired
// recorded - display TV shows that you have previously recorded 
$defaultView = "recorded";

// This is used to determine what the percent of matching
// between mythdb subtitles and tvrage subtitles. i.e Alter Ego Altar Ego
// Going too low will cause a bunch of bogus matches.  Best results are
// 80-90. 100 is exact match.
$matchPercent = 85;

// This is used to determine when the show data should be updated
// this value is in seconds.  i.e. 7 days * 24 hours * 60 minutes * 60 seconds
$maxFileAgeInSeconds = 604800;
 
// Files/Directories used for tvwish_list.php
$listDir    = "$wishDir/episodes";
$masterFile = "$wishDir/master";
$tvwishep   = "$dataDir/episode/tvwish/episodes";

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
