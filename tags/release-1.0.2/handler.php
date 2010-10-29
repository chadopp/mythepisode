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

// Setup some paths
$rootDir   = $_SERVER['DOCUMENT_ROOT'].'/mythweb';
$scriptDir = "$rootDir/modules/episode/utils";
$dataDir   = "$rootDir/data";

// Update include path to include modules/tv classes/includes
ini_set('include_path', ini_get('include_path').':modules/tv');

require_once "includes/init.php";
require_once "includes/sorting.php";

// Directories used for show.php
$showsTxt = "data/episode/shows.txt";
$showsDat = "data/episode/shows.dat";
$getrecorded = 1;

// Directories used for episodes.php
$showDir = "data/episode/shows/";
// This is used to determine what the percent of matching
// between mythdb subtitles and tvrage subtitles. i.e Alter Ego Altar Ego
// Going too low will cause a bunch of bogus matches.  Best results are
// 80-90
$matchPercent = 85;

// Directories used for tvwish_list.php
$listDir    = "data/episode/tvwish/episodes";
$masterFile = "data/episode/tvwish/master";
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
}