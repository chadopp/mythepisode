<?php
/**
 * tvwish listing
 *
 * @date        $Date$
 * @version     $Revision$
 * @author      Author: coppliger
 * @license     GPL
 *
/**/

if ($_GET['wishstr'] || $_POST['wishstr']) {
    unset($_SESSION['wish']);
    $_SESSION['wish']['wishstr'] = _or($_GET['wishstr'], $_POST['wishstr']);
    $_SESSION['wish']['setting'] = _or($_GET['setting'], $_POST['setting']);
    header('Location: tvwish_list');
    exit;
}

// If check boxes are selected create a show file
$showTitle      = str_replace(" ", "", $_POST["title"]);
$cbSelected     = $_POST["f"];
$seriesHeadings = "Series: $showTitle";
$listFile       = "$listDir/$showTitle";

if (count($cbSelected) > 0) {
    $listOut = fopen("$listFile", "w");
    fwrite($listOut, "$seriesHeading\n\n");
    for ($i = 0; $i < count($cbSelected); $i++) {
        fwrite($listOut, "Episode: $cbSelected[$i]\n");
    }
    fclose($listOut);
}

// Find the show lists that are available
$fileDir   = opendir($listDir);
$wishFiles = array();

while (($showFile = readdir($fileDir)) != false) {
    if ($showFile != "." && $showFile != "..") {
        $showFile       = trim($showFile);
        $wishFiles[] = $showFile;
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
} elseif ($_SESSION['wish']['wishstr'] == "deactivate") {
    $out = fopen("$masterFile", "w");
    foreach ($tempFile as $tempEntry) {
        if (!stristr($tempEntry, $state)) {
            fputs($out, $tempEntry);
        }
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

// Load the class for this page
require_once tmpl_dir . 'tvwish_list.php';

// Exit
exit;
?>
