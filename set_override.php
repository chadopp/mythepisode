<?php
/**
 * Display/save mythepisode default settings
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      Author: Chris Kapp
 * @license     GPL
 *
/**/

// Set some directory paths
    $rootDir       = getcwd();
    $dataDir       = "$rootDir/data";
    $epDir         = "$dataDir/episode";
    $showsDat      = "$epDir/shows.dat";
    $showsOverride = "$epDir/override.txt";

// Exit if data files don't exist
    if (!file_exists($showsDat)) {
        custom_error(' Please select TV Episodes first, and then return to configuration.');
        exit;
    }

// Save configuration changes
    if ($_POST['save']) {
        $tvrage = array();
        foreach ($_POST['settings'] as $value => $data) {
            if ($data && array_key_exists($data, $tvrage) && !$_POST['delete'][$value])
                array_push($tvrage[$data], $value);
            elseif ($data && !$_POST['delete'][$value])
                $tvrage[$data] = array($value);
        }
        $f = file($showsOverride);
        $fi = fopen($showsOverride, "w");
        foreach ($tvrage as $show => $matches)
            fwrite($fi, implode("---", $matches).":::".$show."\n");
        fclose($fi);
    }

    $recordedShows = array();
    $recordedShows = unserialize(file_get_contents($showsDat));

// Get a list of previous recordings from the DB
    $recordings = mysql_query("SELECT distinct title 
                                 FROM oldrecorded 
                                WHERE not programid like 'MV%' 
                                  AND (recstatus = '-2' OR recstatus = '-3')") 
                  or trigger_error('SQL Error: ' . mysql_error(), FATAL);

// Put previously recorded shows in an array
    $oldRecorded = array();
    while ($row1 = mysql_fetch_assoc($recordings)) {
        $temp = str_replace(' ', '', strtolower($row1['title']));
        if(!array_key_exists($temp, $recordedShows))
            $oldRecorded[$temp] =  $row1['title'];
    }

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
