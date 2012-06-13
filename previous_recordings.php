<?php
/**
 * previous recordings
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
 /**/

// Classes from modules/tv
    require_once 'classes/Program.php';
    require_once 'includes/sorting.php';

// Load mythepisode includes
    require_once 'includes/previous_utils.php';

// Queries for a specific program title
    isset($_GET['title']) or $_GET['title'] = $_POST['title'];
    isset($_GET['title']) or $_GET['title'] = $_SESSION['previous_recorded_title'];

// Delete a record from the DB if it's not still in the recorded table
    if (!empty($_GET['delete'])) {
        $dbCheck = $db->query('SELECT programid 
                                 FROM recorded
                                WHERE programid=?', $_GET['programid']);

        if ($dbCheck->num_rows() == 1) {
            $Warnings[] = 'Title still exists in Recorded Programs Table';
        } else {
            $deleteRecorded = $db->query('DELETE FROM oldrecorded
                                           WHERE programid=?', $_GET['programid']);
        }
    }

// Parse the program list
    $result = mysql_query("SELECT title,subtitle,description,programid
                             FROM oldrecorded
                            WHERE (recstatus = '-2' OR recstatus = '-3')
                         GROUP BY programid
                         ORDER BY title");

    $All_Shows = array();
    $Programs  = array();

    while (true) {
        $Program_Titles = array();
        while ($record = mysql_fetch_row($result)) {
        // Create a new Data object		
            $show = new Data($record);
        // Assign a reference to this show to the various arrays
            $Program_Titles[$record[0]]++;
            if ($_GET['title'] && $_GET['title'] != $record[0])
                continue;

        // Make sure that everything we're dealing with is an array
            if (!is_array($Programs[$show->title]))
                $Programs[$show->title] = array();
            $All_Shows[] =& $show;
            $Programs[$show->title][] =& $show;
            unset($show);
        }

    // Did we try to view a program that we don't have recorded?
    // Revert to a selection option
        if ($_GET['title'] && !count($Programs)) {
            $Warnings[] = 'No matching programs found.';
            unset($_GET['title']);
            $Program_Titles['- Select a Show']++;
            uksort($Program_Titles, "cmp");
            require_once tmpl_dir . 'previous_recordings.php';
        } else {
            break;
        }
    }

// Sort the program titles
    uksort($Program_Titles, "cmp");
//uksort($Program_Titles, "strnatcasecmp");

// Keep track of the program/title the user wants to view
    $_SESSION['previous_recorded_title'] = $_GET['title'];

// Sort the programs
    if (count($All_Shows))
    //    uksort($All_Shows, "cmp");
sort_programs($All_Shows, 'previous_recorded_sortby');

    if (empty($_GET['title'])) {
        $All_Shows = array();
        unset($_GET['title']);
    }

// Load the class for this page
    require_once tmpl_dir . 'previous_recordings.php';

// Exit
    exit;
