<?php
/**
 * Show listing
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
 /**/

// A single show string passed in
if ($_GET['state']) {
    unset($_SESSION['show']['state']);
    $_SESSION['show']['state'] = $_GET['state'];
} else {
    $_SESSION['show']['state'] = $defaultView;
}

$state = $_SESSION['show']['state'];

// If a shows.txt file doesn't exist or you select update a new list of
// shows will be grabbed from tvrage.com
if (!file_exists($showsTxt) || $state == "update") {
    exec("modules/episode/utils/grabshowsall.pl $showsTxt");
    unset($_SESSION['show']['state']);
    if (file_exists($showsDat))
        unlink($showsDat);
}

if ($state != "recorded") {
    // Read the list of shows from tvrage.com into an array and get total count
    $allShows = file($showsTxt);
    $allCount = count($allShows);

    // Count the number of shows that are currently active TV shows
    foreach ($allShows as $current) {
        $current = explode("\t", $current);
        if ($current[3] == 1) {
            $currentCount = $currentCount + 1;
        }
    }
}

if ($state == "recorded") {
    $recordedShows = array();

    function fixShow($show) {
        return str_replace(' ', '', strtolower($show));
    }

    function explodeShows($item, $key) {
        global $recordedShows;
        $show    = rtrim($item);
        $show    = preg_replace('/<.+?>/', '', $show);
        $show    = explode("\t", $show);
        $show[0] = ucfirst($show[0]);
        $recordedShows[fixShow($show[1])] = $show;
    }

    if (!file_exists($showsDat)) {
        // Read the list of shows from tvrage.com into an array and get total count
        $tempShows = file($showsTxt);

        // convert $tempShows into an associative array
        array_walk($tempShows, 'explodeShows');

        $handle = fopen($showsDat, 'w') or die ("can't open showsDat");
        fwrite($handle, serialize($recordedShows));
        fclose($handle);
    } else {
        $recordedShows = unserialize(file_get_contents($showsDat));
    }
}

// Get a list of previous recordings so we know if a show has had episodes recorded.
$recordings = mysql_query("SELECT distinct title FROM oldrecorded") 
                                  or trigger_error('SQL Error: ' . mysql_error(), FATAL);

// Put previously recorded shows in an array
$oldRecorded = array();
while ($row1 = mysql_fetch_assoc($recordings))
    $oldRecorded[] = str_replace(' ', '', strtolower($row1['title']));

// Override is used for shows that have names that don't matchup properly
// For example mythtv records "Survivor" as "Survivor: Nicaragua".  Since
// the names don't match they won't display properly as recorded and won't
// show sheduled/previous recordings.  The override.txt file located 
// under data/episodes is used to overcome this issue. 
$overrideCount = 0;
if (!file_exists($showsOverride))
    copy("$scriptDir/override.template", "$showsOverride");

$overrideFile = file($showsOverride);
$mythTitle = array();

foreach ($overrideFile as $overrideShow) {
    list($mythName,$rageName) = explode(":::", "$overrideShow");
    $rageName  = trim($rageName);
    $mythName  = trim($mythName);
    $rageName  = str_replace(' ', '', strtolower($rageName));
    $mythName  = str_replace(' ', '', strtolower($mythName));
    $mythTitle = explode("---", "$mythName");
    foreach ($mythTitle as $tempTitle) {
        if (in_array("$tempTitle", $oldRecorded))  {
            array_push($oldRecorded, "$rageName");
            $overrideCount++;
            break; 
        }
    }
}

mysql_free_result($recordings);

sort($oldRecorded);
$recordedCount = count($oldRecorded) - $overrideCount;

// Load the class for this page
require_once tmpl_dir . 'show.php';

// Exit
exit;
?>