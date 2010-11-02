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
$rootDir       = getcwd();
$scriptDir     = "$rootDir/modules/episode/utils";
$dataDir       = "$rootDir/data";
$epDir         = "$dataDir/episode";
$showsDat      = "$epDir/shows.dat";
$showsOverride = "$epDir/override.txt";

// Save configuration changes
if ($_POST['save']) {
    $tvrage = array();
    foreach ($_POST['settings'] as $value => $data) {
        if ($data && array_key_exists($data, $tvrage))
            array_push($tvrage[$data], $value);
        elseif ($data)
            $tvrage[$data] = array($value);
    }
    $f = file($showsOverride);
    $fi = fopen($showsOverride, "w");
    foreach ($tvrage as $show => $matches)
        fwrite($fi, implode("---", $matches).":::".$show."\n");
    fclose($fi);
}

// Copy the override.template to data/episode/override.txt if it doesn't exit 
if (!file_exists($showsOverride))
    copy("$scriptDir/override.template", "$showsOverride");

    $recordedShows = array();

    // Remove whitespace and make lowercase
    function fixShow($show) {
        return str_replace(' ', '', strtolower($show));
    }

    // Cleanup show names
    function explodeShows($item, $key) {
        global $recordedShows;
        $show    = rtrim($item);
        $show    = preg_replace('/<.+?>/', '', $show);
        $show    = explode("\t", $show);
        $show[0] = ucfirst($show[0]);
        $recordedShows[fixShow($show[2])] = $show;
    }

    // Create showsDat if it doesn't exist
    if (!file_exists($showsDat)) {
        // Read the list of shows from tvrage.com into an array
        $tempShows = file($showsTxt);

        // Convert $tempShows into an associative array
        array_walk($tempShows, 'explodeShows');

        // Open showsDat for writing
        $handle = fopen($showsDat, 'w') or die ("can't open showsDat");
        fwrite($handle, serialize($recordedShows));
        fclose($handle);
    } else {
        $recordedShows = unserialize(file_get_contents($showsDat));
    }

// Get a list of previous recordings from the DB
$recordings = mysql_query("SELECT distinct title FROM oldrecorded where not programid like 'MV%'") 
                                  or trigger_error('SQL Error: ' . mysql_error(), FATAL);

// Put previously recorded shows in an array
$oldRecorded = array();
while ($row1 = mysql_fetch_assoc($recordings)) {
    $temp = str_replace(' ', '', strtolower($row1['title']));
    if(!array_key_exists($temp, $recordedShows))
        $oldRecorded[$temp] =  $row1['title'];
}

// Override is used for shows that have names that don't matchup properly
// For example mythtv records "Survivor" as "Survivor: Nicaragua".  Since
// the names don't match they won't display properly as recorded and won't
// show sheduled/previous recordings.  The override.txt file located 
// under data/episodes is used to overcome this issue. 

// Read showsOverride file into an array
$overrideFile  = file($showsOverride);
$mythTitle     = array();
$overrideCount = 0;

// Go through overrideFile array and get the override show titles
foreach ($overrideFile as $overrideShow) {
    list($mythName,$rageName) = explode(":::", "$overrideShow");
    $rageName  = trim($rageName);
    $mythName  = trim($mythName);
    $rageName  = str_replace(' ', '', strtolower($rageName));
    $mythTitle = explode("---", "$mythName");
    // Determine each new show title and add it to oldRecorded array
    foreach ($mythTitle as $tempTitle) {
        $tempTitle  = str_replace(' ', '', strtolower($tempTitle));
        if (array_key_exists($tempTitle, $oldRecorded))
            unset($oldRecorded[$tempTitle]);
    }
}

natsort($overrideFile);
natsort($oldRecorded);
// These settings are limited to Mythepisode itself
$Settings_Hosts = 'Mythepisode';
