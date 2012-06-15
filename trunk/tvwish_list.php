<?php
/**
 * tvwish listing
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Read in a string
    if ($_GET['wishstr'] || $_POST['wishstr']) {
        unset($_SESSION['wish']);
        $_SESSION['wish']['wishstr'] = _or($_GET['wishstr'], $_POST['wishstr']);
        $_SESSION['wish']['setting'] = _or($_GET['setting'], $_POST['setting']);
        header('Location: tvwish_list');
        exit;
    }

// Files/Directories used for tvwish
    $listDir    = "$wishDir/episodes";
    $masterFile = "$wishDir/master";
    $tvwishep   = "$dataDir/episode/tvwish/episodes";

// Create the wish dir if it doesn't exist
    if (!is_dir($wishDir) && !mkdir($wishDir, 0775)) {
        custom_error('Error creating '.$wishDir.': Please check permissions on the data directory.');
        exit;
    }

// Create the list dir if it doesn't exist
    if (!is_dir($listDir) && !mkdir($listDir, 0775)) {
        custom_error('Error creating '.$listDir.': Please check permissions on the data directory.');
        exit;
    }

// Copy the template file over to data/episode/tvwish/master if one doesn't exist
    if (!file_exists($masterFile)) {
        copy("$scriptDir/master.template", "$masterFile");
        custom_error('Initial tvwish master file did not exist.  Master wishfile created...Resave your tvwish list for show '.$_POST['title'].'');
        exit;
    }

// If check boxes are selected create a show file
    $cbSelected    = $_POST["f"];
    $longTitle     = $_POST['title'];
    $seriesHeading = "Series: $longTitle";
    $showTitle     = str_replace(" ", "", $_POST["title"]);
    $listFile      = "$listDir/$showTitle";

    if (count($cbSelected) > 0) {
        $listOut = fopen("$listFile", "w");
        fwrite($listOut, "$seriesHeading\n\n");
        for ($i = 0; $i < count($cbSelected); $i++) {
            fwrite($listOut, "Episode: $cbSelected[$i]\n");
        }
        fclose($listOut);
    }

// Delete a tvwish show file
    if ($_SESSION['wish']['wishstr'] == "delete") {
        $deleteShow = $_SESSION['wish']['setting'];
        $deleteFile = "$tvwishep/$deleteShow";
        if (file_exists($deleteFile)) {
            unlink($deleteFile);
        }
    }

// Find the show lists that are available
    $fileDir   = opendir($listDir);
    $wishFiles = array();

    while (($showFile = readdir($fileDir)) != false) {
        if ($showFile != "." && $showFile != "..") {
            $showFile    = trim($showFile);
            $content     = file("$listDir/$showFile");
            $seriesName  = explode(": ", $content[0]);
            $wishFiles[] = "$showFile::$seriesName[1]";
        }
    }
    closedir($fileDir);
    sort($wishFiles);

// Update the master file with the show lists
    $tempFile = file("$masterFile");
    $state    = $_SESSION['wish']['setting'];

    if ($_SESSION['wish']['wishstr'] == "activate") {
        $out = fopen("$masterFile", "a+");
        fwrite($out, $_SESSION['wish']['setting']);
        fwrite($out, "\n");
        fclose($out);
        unset($_SESSION['wish']);
    } elseif (($_SESSION['wish']['wishstr'] == "deactivate") || ($_SESSION['wish']['wishstr'] == "delete")){
        $out = fopen("$masterFile", "w");
        foreach ($tempFile as $tempEntry) {
            if (!stristr($tempEntry, $state))
                fputs($out, $tempEntry);
        }
        unset($_SESSION['wish']);
        fclose($out);
    }

// Get a list of show lists that are active in master file
    $inputFile = file("$masterFile");
    $pattern   = "Include: $tvwishep/";

    foreach ($inputFile as $fn) {
        $activatedShow[]   = $fn;
        $fn                = ltrim($fn, "#");
        $fn                = str_replace($pattern, '', $fn);
        $fn                = trim($fn);
        $deactivatedShow[] = $fn;
    }
    sort($activatedShow);

// Load the class for this page
    require_once tmpl_dir . 'tvwish_list.php';

// Exit
    exit;
