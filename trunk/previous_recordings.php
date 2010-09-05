<?php
/**
 * previous recordings
 *
 * @date        $Date$
 * @version     $Revision$
 * @author      Author: coppliger
 * @license     GPL
 *
 /**/

/***                                                                        ***\
view and delete previously recorded programs from the database.
\***                                                                        ***/

// Queries for a specific program title
isset($_GET['title']) or $_GET['title'] = $_POST['title'];
isset($_GET['title']) or $_GET['title'] = $_SESSION['previous_recorded_title'];

function StripString($rStr, $StripText) {
    $rTempStr = explode($StripText, $rStr);
    $rStr     = implode("", $rTempStr);
    return $rStr;
}

// Delete a record from the DB
if (!empty($_GET['delete']))
    $deleteRecorded = $db->query('DELETE FROM oldrecorded
                                  WHERE programid=?', $_GET['category']);

// Parse the program list
$result = mysql_query('SELECT title,subtitle,description,programid FROM oldrecorded GROUP BY programid');

$Total_Programs = 0;
$All_Shows      = array();
$Programs       = array();

while (true) {
    $Program_Titles = array();
    while ($record = mysql_fetch_row($result)) {
        // Create a new program object
        $show = new Program($record);
        // Assign a reference to this show to the various arrays
        $Total_Programs++;
        $Program_Titles[$record[0]]++;
        $Groups[$record[30]]++;
        if ($_GET['title'] && $_GET['title'] != $record[0])
            continue;
        if ($_GET['recgroup'] && $_GET['recgroup'] != $record[30])
            continue;
        
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
        $Warnings[] = 'No matching programs found.  Showing all programs.';
        unset($_GET['title']);
        $_GET['title'] = $record[0];
    } else {
        break;
    }
}

// Sort the program titles
uksort($Program_Titles, "strnatcasecmp");

// Keep track of the program/title the user wants to view
$_SESSION['previous_recorded_title'] = $_GET['title'];


// Sort the programs
if (count($All_Shows))
    sort_programs($All_Shows, 'previous_recorded_sortby');

if (empty($_GET['title'])) {
    $All_Shows = array();
    unset($_GET['title']);
}

// Load the class for this page
require_once tmpl_dir . 'previous_recordings.php';

// Exit
exit;
?> 
