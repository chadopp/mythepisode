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

// Load classes from modules/tv
require_once 'classes/Schedule.php';
require_once 'classes/Channel.php';
require_once 'classes/Program.php';
require_once 'includes/recording_schedules.php';

// Load the sorting routines
require_once 'includes/sorting.php';

// Strings passed in to identify showname, showstring, longshow to grab from tvrage.com
if ($_GET['showstr'] || $_POST['showstr']) {
    unset($_SESSION['search']);
    $_SESSION['search']['showstr']  = _or($_GET['showstr'], $_POST['showstr']);
    $_SESSION['search']['showname'] = _or($_GET['showname'], $_POST['showname']);
    $_SESSION['search']['longshow'] = _or($_GET['longshow'], $_POST['longshow']);
    $allEpisodes = "all";
}

// If state is update we need to update episode file
if ($_GET['state'] || $_POST['state']) {
    $_SESSION['search']['state'] = _or($_GET['state'], $_POST['state']);
}

// Grab the allepisodes string if it exists
if ($_GET['allepisodes'] || $_POST['allepisodes']) {
    unset($_SESSION['episodes']);
    $_SESSION['episodes']['allepisodes'] = _or($_GET['allepisodes'], $_POST['allepisodes']);
    $allEpisodes = $_SESSION['episodes']['allepisodes'];
} else {
    unset($_SESSION['episodes']['title']);
    $_SESSION['episodes']['allepisodes'] = "all";
}   
 

// Get the episode title used to query the DB for previous recordings
if ($_GET['title'] || $_POST['title']) {
    $_SESSION['episodes']['title'] = _or($_GET['title'], $_POST['title']);
    unset($_SESSION['episodes']['allepisodes']);
    $recordedTitle = $_GET['title'];
}

// Create the images dir if it doesn't exist
if (!is_dir($imageDir) && !mkdir($imageDir, 0775)) {
    custom_error('Error creating '.$imageDir.': Please check permissions on the data directory.');
    exit;
}

// Delete a record from the DB
if (!empty($_GET['delete']))
    $deleteRecorded = $db->query('DELETE FROM oldrecorded
                                   WHERE programid=?', $_GET['category']);

// Set some variables
$Total_Programs = 0;
$All_Shows      = array();
$Programs       = array();
$showTitle      = $_SESSION['search']['showstr'];
$longTitle      = $_SESSION['search']['longshow'];
$fixedTitle     = $showTitle;
$showTitle      = preg_replace("/^The /", '', $showTitle);
$state          = $_SESSION['search']['state'];
$showFilename   = preg_replace('/\s+/', '', $_SESSION['search']['showname']);
$showFilename   = trim($showFilename);
$showPath       = "$showDir/$showFilename";
$toggleSelect   = "false";
$schedEpisodes  = array();
$schedDate      = array();
$schedEpisodesDetails  = array();

// Override is used for shows that have names that don't matchup properly
// For example mythtv records "Survivor" as "Survivor: Nicaragua".  Since
// the names don't match they won't display properly as recorded and won't
// show sheduled/previous recordings.  The override.txt file located
// under data/episode is used to overcome this issue.  See the README for
// more details
$overrideFile = file($showsOverride);
$mythName = array("$showTitle");

// Go thru the overridFile and get a list of shows to override and split
// them  into titles
foreach ($overrideFile as $overrideShow) {
    list($mythTemp,$rageName) = explode(":::", "$overrideShow");
    $rageName = rtrim($rageName);
    if ($rageName == $fixedTitle)  {
        $mythName = explode("---", "$mythTemp");
        break;
    }
}

// If the showTitle is defined look for scheduled recordings
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
                //echo "Sheduled ...$show->title...<br>";
                //echo "ShowTitle ...$showTitle... - mythtitle ...$show->title...<br>";
                $show->title = preg_replace("/^The/", '', $show->title);
                $show->title = ltrim($show->title, " ");
                foreach ($mythName as $mythShow) { 
                    $mythShow = preg_replace("/^The/", '', $mythShow); 
                    $mythShow = ltrim($mythShow, " ");
                    if (strtolower($mythShow) != strtolower($show->title))
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
                        'NeverRecord',
                        'PreviousRecording',
                        'CurrentRecording',
                        'EarlierShowing',
                        'LaterShowing'
                    ))) {
                        continue;
                    }
                    // print "Sched Episode is $show->subtitle - at $show->airdate - $show->recstatus<BR>";
                    // Assign a reference for this show to the various arrays
                    $schedDate[]     = $show->airdate;
                    $schedEpisodes[] = strtolower($show->subtitle);
                    $schedEpisodes   = preg_replace('/[^0-9a-z ]+/i', '', $schedEpisodes);
                    $schedEpisodes   = preg_replace('/[^\w\d\s]+­/i', '', $schedEpisodes);
                    $schedEpisodes   = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $schedEpisodes);
                    $schedEpisodes   = preg_replace('/\s+/', '', $schedEpisodes);
                    $schedEpisodes   = preg_replace('/[\/\;]/', '', $schedEpisodes);
                    $schedEpisodesDetails[] = array(
                            "syndicatedepisodenumber" => substr($show->syndicatedepisodenumber,0,1)."-".substr($show->syndicatedepisodenumber,1),
                            "airdate" => $show->airdate,
                            "subtitle" => $show->subtitle,
                            "description" => $show->description,
                            "matched" => false);
                }
            }
        }
    }
    $totalSched = count($schedEpisodes);

    // check to see if the data should be refreshed from tvrage.
    // if the file doesnècontain INFO, and the show is still current
    // and the data is over 7 days old get the new file
    $updateFile=false;
    if (file_exists($showPath)) {
        $episodeInfo = file($showPath);
        if (preg_match('/^INFO/', $episodeInfo[0])) {
            list(,$showId,$showStart,$showEnd,$showCtry,$showStatus,
                 $showClass,$showGenre,$showNetwork,$showLink,$showSummary) = explode(":", $episodeInfo[0]);
            if ($showEnd=="" && (time() - filemtime($showPath)) > $maxFileAgeInSeconds)
                $updateFile=true;
        } else {
            $updateFile=true;
        }
    }
 	
    // Update the episodes list for passed in title by grabbing episodes from tvrage
    if (!file_exists($showPath) || $state == "update" || $updateFile) {
        exec("modules/episode/utils/grabid.pl \"$longTitle\" \"$showPath\" \"$imageDir\"");
        unset($_SESSION['search']['state']);
        $allEpisodes = "all";
    }

    // Setup the title query string bases off of mythName array.
    foreach ($mythName as $queryString) {
        $queryString = mysql_real_escape_string($queryString);
        if (!$titleQuery) {
            $titleQuery = "title like '%{$queryString}'";
        } else {
            $titleQuery = "$titleQuery OR title like '%{$queryString}'";
        }
    }

    // Check the DB for any episodes of the show previously recorded
    $getSubtitles = mysql_query("SELECT subtitle,starttime 
                                   FROM oldrecorded 
                                  WHERE ($titleQuery) 
                                    AND (recstatus = '-2' OR recstatus = '-3')
                               Group BY programid");

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

    // Check the DB for any episodes of the show available AND watched
    $getSubtitles = mysql_query("SELECT subtitle,starttime 
                                   FROM recorded 
                                  WHERE ($titleQuery) 
                                    AND watched = '1'
                               Group BY basename");

    $watchedEpisodes = array();
    $watchedDate     = array();
    while ($row = mysql_fetch_assoc($getSubtitles)) {
        $watchedDate[]     = date('Y-m-d', strtotime($row['starttime']));
        $watchedEpisodes[] = strtolower($row['subtitle']);
    }

    mysql_free_result($getSubtitles);
    $watchedEpisodes   = preg_replace('/[^0-9a-z ]+/i', '', $watchedEpisodes);
    $watchedEpisodes   = preg_replace('/[^\w\d\s]+­/i', '', $watchedEpisodes);
    $watchedEpisodes   = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $watchedEpisodes);
    $watchedEpisodes   = preg_replace('/\s+/', '', $watchedEpisodes);
    
    // Check the DB for any episodes of the show available AND unwatched
    $getSubtitles = mysql_query("SELECT subtitle,starttime 
                                   FROM recorded 
                                  WHERE ($titleQuery) 
                                    AND (watched = '0')
                               Group BY basename");

    $unwatchedEpisodes = array();
    $unwatchedDate     = array();
    while ($row = mysql_fetch_assoc($getSubtitles)) {
        $unwatchedDate[]     = date('Y-m-d', strtotime($row['starttime']));
        $unwatchedEpisodes[] = strtolower($row['subtitle']);
    }

    mysql_free_result($getSubtitles);
    $unwatchedEpisodes   = preg_replace('/[^0-9a-z ]+/i', '', $unwatchedEpisodes);
    $unwatchedEpisodes   = preg_replace('/[^\w\d\s]+­/i', '', $unwatchedEpisodes);
    $unwatchedEpisodes   = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $unwatchedEpisodes);
    $unwatchedEpisodes   = preg_replace('/\s+/', '', $unwatchedEpisodes);

    // Get information about shows to display on the top right
    // of the episode listing page
    $episodeInfo = file($showPath);
    if (preg_match('/^INFO/', $episodeInfo[0])) {
        list(,$showId,$showStart,$showEnd,$showCtry,$showStatus,
              $showClass,$showGenre,$showNetwork,$showLink,$showSummary) = explode(":", $episodeInfo[0]);
        $showData = "<p align=left><strong>$longTitle</strong><br><br>$showSummary</p>";
        if (!$showLink) $showLink = "//www.tvrage.com";
        $totalEpisodes = count($showEpisodes) - 1;
    }
}

// Get a list of episodes for shows that have been recorded in the past to display
// on the previous recordings page.
if ($recordedTitle) {
    // Parse the program list
    $result = mysql_query("SELECT title,subtitle,description,programid,starttime 
                             FROM oldrecorded 
                            WHERE ($titleQuery) 
                              AND (recstatus = '-2' OR recstatus = '-3')
                         GROUP BY programid");

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
        sort_programs($All_Shows, 'episode_sortby');

}

// Load the class for this page
require_once tmpl_dir . 'episodes.php';

// Exit
exit;
?>
