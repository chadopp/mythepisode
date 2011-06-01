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

// Get MythTv verson 
    if ($_POST['mythtv_version']) {
        $mythVersion = $_POST['mythtvVersion'];
    } else {
        $mythVersion = $mythtvVersion;
    }
// Load classes from modules/tv
    if ($mythVersion <= .23) {
        require_once 'classes/Schedule.php';
        require_once 'classes/Channel.php';
        require_once 'classes/Program.php';
        require_once 'includes/recording_schedules.php';
    } else {
        require_once 'includes/recording_schedules.php';
        require_once 'classes/Channel.php';
        require_once 'classes/Program.php';
        require_once 'classes/Schedule.php';
    }

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

// Get Query site name i.e. TVRage.com or TheTVDB.com
    if ($_POST['display_site']) {
        $sitePage = $_POST['display_site'];
    } else {
        $sitePage = $defaultSite;
    }

// Create the cache dir if it doesn't exist
    if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775)) {
        custom_error('Error creating '.$cacheDir.': Please check permissions on the data directory.');
        exit;
    }

// Create the images dir if it doesn't exist
    if (!is_dir($imageDir) && !mkdir($imageDir, 0775)) {
        custom_error('Error creating '.$imageDir.': Please check permissions on the data directory.');
        exit;
    }

// Delete a record from the DB only after we make sure it's not in
// the recorded table
    if (!empty($_GET['delete'])) {
        $dbCheck = $db->query('SELECT programid FROM recorded
                                WHERE programid=?', $_GET['category']);
        if ($dbCheck->num_rows() == 1) {
            $Warnings[] = 'Title still exists in Recorded Programs Table';
        } else {
            $deleteRecorded = $db->query('DELETE FROM oldrecorded
                                           WHERE programid=?', $_GET['category']);
        }
    }

// Mark an episode as recorded in the DB
    if (!empty($_GET['mark'])) {
        $allEpisodes = "all";
        $_SESSION['episodes']['allepisodes'] = "all";
        $markTitle = mysql_real_escape_string($_GET['marktitle']);
        $markSub = mysql_real_escape_string($_GET['marksubtitle']);
        $markAirdate = $_GET['markairdate'];
        $markSummary = $_GET['marksummary'];
        $today = date("YmdHis");
        $programId = "ME$today";
        //echo "Title    - $markTitle<BR>";
        //echo "Subtitle - $markSub<BR>";
        //echo "Airdate  - $markAirdate<BR>";
        //echo "Summary  - $markSummary<BR>";
        //echo "ProgramID - $programId<BR>";
        $markRecorded = $db->query('INSERT INTO oldrecorded
                                       SET starttime   = ?,
                                           duplicate   = 1,
                                           chanid      = 9999,
                                           recstatus   = -3,
                                           title       = ?,
                                           subtitle    = ?,
                                           description = ?,
                                           programid   = ?',
                                           $markAirdate,
                                           $markTitle,
                                           $markSub,
                                           $markSummary,
                                           $programId);
    }

// Set some variables
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
    $cacheShowname  = "$cacheDir/$showFilename";
    $toggleSelect   = "false";
    $schedEpisodes  = array();
    $schedDate      = array();
    $maxFileAgeSec  = ($maxFileAge * 24 * 60 * 60);
    $schedEpisodesDetails = array();

// Get value of subtitle matching checkbox
    if (!$_POST['subtitle_match'] && $_GET['subMatch']) {
        if (file_exists($cacheShowname)) unlink($cacheShowname);
        $subMatchDis = 0;
        unset($_GET['subMatch']);
    } elseif ($_GET['subMatch'] || file_exists($cacheShowname)) {
        touch($cacheShowname);
        $subMatchDis = 1;
    }

// Override is used for shows that have names that don't matchup properly
// For example mythtv records "Survivor" as "Survivor: Nicaragua".  Since
// the names don't match they won't display properly as recorded and won't
// show sheduled/previous recordings.  The override.txt file located
// under data/episode is used to overcome this issue.  See the README for
// more details
    $overrideFile = file($showsOverride);
    $mythName     = array("$showTitle");

// Go thru the overridFile and get a list of shows to override and split
// them  into titles
    foreach ($overrideFile as $overrideShow) {
        list($mythTemp,$rageName) = explode(":::", "$overrideShow");
        $rageName = rtrim($rageName);
        if ($rageName == $longTitle)  {
            $mythName = explode("---", "$mythTemp");
            break;
        }
    }

// If showTitle is defined look for scheduled recordings
    if ($showTitle) {
    // Parse the list of scheduled recordings
        $all_shows = array();
        if ($mythVersion <= .23) {
            global $Scheduled_Recordings;
            $schedRecords = $Scheduled_Recordings; 
        } else {
            $scheduledRecordings = Schedule::findScheduled();
            $schedRecords = $scheduledRecordings;
        }
        foreach ($schedRecords as $callsign => $shows) {
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
                        $startTime = date('Y-m-d', $show->starttime);
                        $schedDate[]        = $startTime;
                        $schedEpisodesTitle = strtolower($show->subtitle);
                        $schedEpisodesTitle = preg_replace('/[^0-9a-z ]+/i', '', $schedEpisodesTitle);
                        $schedEpisodesTitle = preg_replace('/[^\w\d\s]+­/i', '', $schedEpisodesTitle);
                        $schedEpisodesTitle = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $schedEpisodesTitle);
                        $schedEpisodesTitle = preg_replace('/\s+/', '', $schedEpisodesTitle);
                        $schedEpisodesTitle = preg_replace('/[\/\;]/', '', $schedEpisodesTitle);
                        $schedEpisodes[]    = $schedEpisodesTitle;
                        if(!array_key_exists($schedEpisodesTitle, $schedEpisodesDetails)) {
                            $schedEpisodesDetails[$schedEpisodesTitle]=array(
                            "syndicatedepisodenumber" => substr($show->syndicatedepisodenumber,0,1)."-".substr($show->syndicatedepisodenumber,1),
                            "airdate"     => $startTime,
                            "subtitle"    => $show->subtitle,
                            "description" => $show->description,
                            "matched"     => false);
                        }
                    }
                }
            }
        }
        $totalSched = count($schedEpisodes);

    // check to see if the data should be refreshed from tvrage.
    // if the file doesn't contain INFO, and the show is still current
    // and the data is over $maxFileAgeSec days old get the new file
        $updateFile=false;
        if (file_exists($showPath)) {
            $episodeInfo = file($showPath);
            if (preg_match('/^INFO/', $episodeInfo[0])) {
                list($showInfo,$showId,$showStart,$showEnd,$showCtry,$showStatus,
                     $showClass,$showGenre,$showNetwork,$showLink,$showSummary) = explode(":", $episodeInfo[0]);
                if ($showInfo == "INFOTVDB") {
                    $config['siteSelect'] = "TheTVDB.com";
                } else {
                    $config['siteSelect'] = "TVRage.com";
                } 
                if ($showEnd=="" && (time() - filemtime($showPath)) > $maxFileAgeSec)
                    $updateFile=true;
            } else {
                $updateFile=true;
            }
        }
 	
    // Update the episodes list for passed in title by grabbing episodes from tvrage
        if (!file_exists($showPath) || $state == "update" || $updateFile) {
            exec("modules/episode/utils/grabid.pl \"$longTitle\" \"$showPath\" \"$imageDir\" \"$sitePage\"");
            unset($_SESSION['search']['state']);
            $allEpisodes = "all";
        }

    // Setup the title query string based off of mythName array.
        foreach ($mythName as $queryString) {
            $queryString = mysql_real_escape_string($queryString);
            if (!$titleQuery) {
                $titleQuery = "title like '%{$queryString}'";
            } else {
                $titleQuery = "$titleQuery OR title like '%{$queryString}'";
            }
        }

    // Check the DB for any episodes of the show previously recordeo
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
        $recEpisodes = preg_replace('/[^0-9a-z ]+/i', '', $recEpisodes);
        $recEpisodes = preg_replace('/[^\w\d\s]+­/i', '', $recEpisodes);
        $recEpisodes = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $recEpisodes);
        $recEpisodes = preg_replace('/\s+/', '', $recEpisodes);

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
        $watchedEpisodes = preg_replace('/[^0-9a-z ]+/i', '', $watchedEpisodes);
        $watchedEpisodes = preg_replace('/[^\w\d\s]+­/i', '', $watchedEpisodes);
        $watchedEpisodes = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $watchedEpisodes);
        $watchedEpisodes = preg_replace('/\s+/', '', $watchedEpisodes);
    
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
        $unwatchedEpisodes = preg_replace('/[^0-9a-z ]+/i', '', $unwatchedEpisodes);
        $unwatchedEpisodes = preg_replace('/[^\w\d\s]+­/i', '', $unwatchedEpisodes);
        $unwatchedEpisodes = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $unwatchedEpisodes);
        $unwatchedEpisodes = preg_replace('/\s+/', '', $unwatchedEpisodes);

    // Check the DB for any videos of the show available
        $getSubtitles = mysql_query("SELECT subtitle,releasedate,season,episode
                                       FROM videometadata 
                                      WHERE ($titleQuery) 
                                   Group BY filename");

        $videoEpisodes = array();
        $videoDate     = array();
        $videoSE       = array();

    // If we find videos in the DB
        if ($getSubtitles) {
            while ($row = mysql_fetch_assoc($getSubtitles)) {
                $videoDate[]     = date('Y-m-d', strtotime($row['releasedate']));
                $videoEpisodes[] = strtolower($row['subtitle']);
                $videoSE[]       = (string) ($row['season']."-".str_pad($row['episode'], 2, "0", STR_PAD_LEFT));
            }

            mysql_free_result($getSubtitles);
            $videoEpisodes = preg_replace('/[^0-9a-z ]+/i', '', $videoEpisodes);
            $videoEpisodes = preg_replace('/[^\w\d\s]+­/i', '', $videoEpisodes);
            $videoEpisodes = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $videoEpisodes);
            $videoEpisodes = preg_replace('/\s+/', '', $videoEpisodes);
        }

    // Get information about shows to display on the top right
    // of the episode listing page
        $episodeInfo = file($showPath);
        if (preg_match('/^INFO/', $episodeInfo[0])) {
            list($showInfo,$showId,$showStart,$showEnd,$showCtry,$showStatus,
                  $showClass,$showGenre,$showNetwork,$showLink,$showSummary) = explode(":", $episodeInfo[0]);
            if ($showInfo == "INFOTVDB") {
                $config['siteSelect'] = "TheTVDB.com";
            } else {
                $config['siteSelect'] = "TVRage.com";
            } 
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
            while ($record = mysql_fetch_row($result)) {
            // Create a new program object
                $show = new Program($record);
            
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
    
    // Sort the programs
        if (count($All_Shows))
            sort_programs($All_Shows, 'episode_sortby');

    }

// Get the date that show info was last updated from tvrage or thetvdb 
    clearstatcache();
    $fileTime = date("Y-m-d", filemtime($showPath));  

// Load the class for this page
    require_once tmpl_dir . 'episodes.php';

// Exit
    exit;
