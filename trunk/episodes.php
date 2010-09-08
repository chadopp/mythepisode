<?php
/**
 * episode listing
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL 
 *
 /**/

// Classes from modules/tv
require_once 'classes/Schedule.php';
require_once 'classes/Channel.php';
require_once 'classes/Program.php';
require_once 'includes/recording_schedules.php';

// Two strings passed in to identify showname and showstring to grab from tvrage.com
if ($_GET['showstr'] || $_POST['showstr']) {
    unset($_SESSION['search']);
    $_SESSION['search']['showstr']  = _or($_GET['showstr'], $_POST['showstr']);
    $_SESSION['search']['showname'] = _or($_GET['showname'], $_POST['showname']);
    $allEpisodes = "all";
}

// If state is update we need to update episode file
if ($_GET['state'] || $_POST['state']) {
    $_SESSION['search']['state'] = _or($_GET['state'], $_POST['state']);
}

if ($_GET['allepisodes'] || $_POST['allepisodes']) {
    unset($_SESSION['episodes']);
    $_SESSION['episodes']['allepisodes'] = _or($_GET['allepisodes'], $_POST['allepisodes']);
    $allEpisodes = $_SESSION['episodes']['allepisodes'];
} else {
    unset($_SESSION['episodes']['title']);
    $_SESSION['episodes']['allepisodes'] = "all";
}   
 

// Queries for a specific program title that were previously recorded
if ($_GET['title'] || $_POST['title']) {
    $_SESSION['episodes']['title'] = _or($_GET['title'], $_POST['title']);
    unset($_SESSION['episodes']['allepisodes']);
    $recordedTitle = $_GET['title'];
}

function StripString($rStr, $StripText) {
    $rTempStr = explode($StripText, $rStr);
    $rStr     = implode("", $rTempStr);
    return $rStr;
}

// Delete a record from the DB
if (!empty($_GET['delete']))
    $deleteRecorded = $db->query('DELETE FROM oldrecorded
                                   WHERE programid=?', $_GET['category']);

$Total_Programs = 0;
$All_Shows      = array();
$Programs       = array();

$showTitle      = $_SESSION['search']['showstr'];
$showTitle      = addslashes($showTitle);
$showTitle      = preg_replace('/ \(US\)/', '', $showTitle);
$state          = $_SESSION['search']['state'];
$showFilename   = preg_replace('/\s+/', '', $_SESSION['search']['showname']);
$showFilename   = trim($showFilename);
$showPath       = "$showDir/$showFilename";
$toggleSelect   = "false";
$schedEpisodes  = array();
$schedDate      = array();

if ($showTitle) {
    // Parse the list of scheduled recordings
    global $Scheduled_Recordings;
    $all_shows = array();
    foreach ($Scheduled_Recordings as $callsign => $shows) {
    //foreach (Schedule::findScheduled() as $callsign => $shows) {
        foreach ($shows as $starttime => $show_group) {
            // Skip things we've already recorded
            if ($starttime <= time())
                continue;
            // Parse each show group
            foreach ($show_group as $key => $show) {
                //echo "ShowTitle $showTitle - mythtitle $show->title<br>";
                if (strtolower($showTitle) != strtolower($show->title))
                    continue;
                // Make sure this is a valid show (ie. skip in-progress recordings and other junk)
                if (!$callsign || $show->length < 1)
                    continue;
                // Skip conflicting shows?
                elseif (in_array($show->recstatus, array(
                    'Conflict',
                    'Overlap'
                ))) {
                    continue;
                }
                // Skip duplicate shows?
                    elseif (in_array($show->recstatus, array(
                    'DontRecord',
                    'PreviousRecording',
                    'CurrentRecording',
                    'EarlierShowing',
                    'LaterShowing'
                ))) {
                    continue;
                }

                // Assign a reference for this show to the various arrays
                $schedDate[]     = $show->airdate;
                $schedEpisodes[] = strtolower($show->subtitle);
                $schedEpisodes   = preg_replace('/[^0-9a-z ]+/i', '', $schedEpisodes);
                $schedEpisodes   = preg_replace('/[^\w\d\s]+­/i', '', $schedEpisodes);
                $schedEpisodes   = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $schedEpisodes);
                $schedEpisodes   = preg_replace('/\s+/', '', $schedEpisodes);
                $schedEpisodes   = preg_replace('/[\/\;]/', '', $schedEpisodes);
            }
        }
    }
    $totalSched = count($schedEpisodes);

    // Update the episodes list for passed in title
    if (!file_exists($showDir . "/" . $showFilename) || $state == "update") {
        exec("modules/episode/utils/grabid.pl \"$showTitle\" \"$showPath\"");
        unset($_SESSION['search']['state']);
        $allEpisodes = "all";
    }

    // Check the DB for any episodes of the show previously recorded
    $getSubtitles = mysql_query(sprintf("SELECT subtitle,starttime
                                           FROM oldrecorded where title like '%s' 
                                          GROUP BY programid", mysql_real_escape_string($showTitle)));

    $recEpisodes = array();
    $recDate     = array();
    while ($row = mysql_fetch_assoc($getSubtitles)) {
        $recDate[]     = date('Y-m-d', strtotime($row['starttime']));
        $recEpisodes[] = strtolower($row['subtitle']);
    }

    mysql_free_result($getSubtitles);
    $recEpisodes   = preg_replace('/[^0-9a-z ]+/i', '', $recEpisodes);
    $recEpisodes   = preg_replace('/[^\w\d\s]+­/i', '', $recEpisodes);
    $recEpisodes   = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $recEpisodes);
    $recEpisodes   = preg_replace('/\s+/', '', $recEpisodes);

    $totalRecorded = count($recEpisodes);
    $showEpisodes  = file($showDir . "/" . $showFilename);
    $totalEpisodes = count($showEpisodes);
}

// Get a list of episodes for shows that have been recorded in the past.
if ($recordedTitle) {

    // Parse the program list
    $result = mysql_query(sprintf("SELECT title,subtitle,description,programid,starttime 
                                     FROM oldrecorded where title like '%s' 
                                    GROUP BY programid", mysql_real_escape_string($recordedTitle)));

    while (true) {
        $Program_Titles = array();
        while ($record = mysql_fetch_row($result)) {
            // Create a new program object
            $show = new Program($record);
            // Assign a reference to this show to the various arrays
            $Total_Programs++;
            $Program_Titles[$record[0]]++;
            
            // Make sure that everything we're dealing with is an array
            if (!is_array($Programs[$show->title]))
                $Programs[$show->title] = array();
                $All_Shows[] =& $show;
                $Programs[$show->title][] =& $show;
                unset($show);
        }
        
        // Did we try to view a program that we don't have recorded?
        // Revert to showing all programs
        if ($_GET['title'] && !count($Programs)) {
            $Warnings[] = 'No recordings found!';
            unset($_GET['title']);
        } else {
            break;
        }
    }
    
    // Sort the program titles
    ksort($Program_Titles);
    
    // Sort the programs
    if (count($All_Shows))
        sort_programs($All_Shows, 'previous_recorded_sortby');

}

// Load the class for this page
require_once tmpl_dir . 'episodes.php';

// Exit
exit;
?>
