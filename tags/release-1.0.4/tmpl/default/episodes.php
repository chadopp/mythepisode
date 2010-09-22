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

// Set the desired page title
$page_title = 'MythWeb - '.t('TV Episodes');

// Mythweb headers
$headers[] = '<link rel="stylesheet" type="text/css"      href="'.skin_url.'/tv_upcoming.css">';

// Print the page header
require 'modules/_shared/tmpl/'.tmpl.'/header.php';

global $All_Shows, $Total_Programs;
global $show, $allEpisodes, $schedDate;
global $showEpisodes, $recEpisodes, $schedEpisodes, $recDate;
global $toggleSelect, $showTitle, $matchPercent;
global $totalRecorded, $totalSched, $totalEpisodes;
$remainingEpisodes = $totalEpisodes-$totalRecorded;
$fixedTitle = stripslashes($fixedTitle);
$showTitle = stripslashes($showTitle);


function imageResize($width, $height, $target) {

// Takes the larger size of the width and height and applies the  
// formula accordingly...this is so this script will work  
// dynamically with any size image

if ($width > $height) {
    $percentage = ($target / $width);
} else {
    $percentage = ($target / $height);
}

// Gets the new value and applies the percentage, then rounds the value
$width  = round($width * $percentage);
$height = round($height * $percentage);

// Returns the new sizes in html image tag format...this is so you
// can plug this function inside an image tag and just get the

return "width=\"$width\" height=\"$height\"";

} 

// Get the image size of the picture and load it into an array
if (file_exists("$imageDir/$showId.jpg")) {
    $imageInfo = getimagesize("$imageDir/$showId.jpg"); 
} else {
    if (!file_exists("$imageDir/noImage.jpg"))
        copy("$scriptDir/noImage.jpg", "$imageDir/noImage.jpg");
    $showId = "noImage";
    $imageInfo = getimagesize("$imageDir/noImage.jpg");
}

?>

<style type="text/css">
td.x-active {
    padding:            .35em .5em;
    border-left:        1px solid #304943;
    height:             2em;
    background-color:   #485;
}
</style>

<script> 
function my_select() { 
    frm = document.forms.test; 
    ele = frm["f[]"];
    len = ele.length;
    type = true;
    for (i = 0; i < len; i++) {
        if (ele[i].checked == true) {
            type = false;
            break;
        }
    }
    for (i = 0; i < len; i++) { 
        ele[i].checked = type;
    }
}
</script> 

<table width="100%" border="0" cellpadding="0" cellspacing="0" >
  <td width="20%" align="center">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" >
    <tr>
      <img src="data/episode/images/<?php echo $showId?>.jpg" 
      <?php echo imageResize($imageInfo[0], $imageInfo[1], 170); ?>>
    </tr>
  </table>
  </td>

  <td width="60%">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td>
    <table width="100%" border="0" cellpadding="0" cellspacing="0" >
      <tr align="center">
        <td>
          <font size=5> 
            <?php echo "$fixedTitle"?>
          </font>
        </td>
      </tr>
      <tr align="center">
        <td>
          <a href="http://www.tvrage.com">
          <?php echo t('Listing Source: www.tvrage.com') ?>
          </a>
          &nbsp;&nbsp; - &nbsp;&nbsp;
          <a href="episode/episodes?state=update">
          <?php echo t('Update Episode Listing') ?></a>
        </td>
      </tr>
    </table>
    </td>
  </tr>

<form id="change_display" action="episode/episodes" method="post">
<div><input type="hidden" name="change_display" value="1"></div>

  <tr>
    <td>
    <table id="display_options" class="commandbox commands" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="x-title"><?php echo t('Display') ?>:</td>
          <?php if ($_SESSION['episodes']['allepisodes'] == "all") { $bgcolor="x-active"; } else { $bgcolor="x-check"; } ?>
        <td class=<?php echo "$bgcolor"?>>
          <a href="episode/episodes/?allepisodes=all"> 
          <?php echo t('All Episodes') ?>:<?php echo " $totalEpisodes"?>
        </td>
        <?php if ($_SESSION['episodes']['title']) { $bgcolor="x-active"; } else { $bgcolor="x-check"; } ?>
        <td class=<?php echo "$bgcolor"?>>
          <a href="episode/episodes/?title=<?php echo $showTitle?>">
          <?php echo t('Recorded') ?>:<?php echo " $totalRecorded"?>
        </td>
        <?php if ($_SESSION['episodes']['allepisodes'] == "none") { $bgcolor="x-active"; } else { $bgcolor="x-check"; } ?>
        <td class=<?php echo "$bgcolor"?>>
          <a href="episode/episodes/?allepisodes=none">
          <?php echo t('Not Recorded') ?>:<?php echo " $remainingEpisodes"?>
        </td>
        <?php if ($_SESSION['episodes']['allepisodes'] == "sched") { $bgcolor="x-active"; } else { $bgcolor="x-check"; } ?>
        <td class=<?php echo "$bgcolor"?>>
          <a href="episode/episodes/?allepisodes=sched">
          <?php echo t('Scheduled') ?>:<?php echo " $totalSched"?>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  </table>
  </td>
  <td width="20%" align="center">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" >
    <tr><td>Start Date:</td><td><?php echo "$showStart"?></td></tr>
    <tr><td>End Date:</td><td><?php echo "$showEnd"?></td></tr>
    <tr><td>Country:</td><td><?php echo "$showCtry"?></td></tr>
    <tr><td>Status:</td><td><?php echo "$showStatus"?></td></tr>
    <tr><td>Classification:</td><td><?php echo "$showClass"?></td></tr>
    <tr><td>Genre:</td><td><?php echo "$showGenre"?></td></tr>
    <tr><td>Network:</td><td><?php echo "$showNetwork"?></td></tr>
  </table>
  </td>
</table>
</form>

<?php
if (isset($_SESSION['episodes']['allepisodes'])) { 

?>

    <form name="test" action="episode/tvwish_list" method="post">
    <table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
      <tr class="menu" align="left">
        <td>Select</td>
        <td>Episode Number</td>
        <td>Original Airdate</td>
        <td>Subtitle</td>
        <td>Synopsis</td>
        <td>Status</td>	
      </tr>

    <?php

    //The purpose of this function is to match shows that have a very
    //similar subtitle. Ex. Altar Ego - Alter Ego
    function close_match($key, $arrayvalue, $matchPercent) {
        foreach ($arrayvalue as $match) {
            similar_text($match, $key, $p);
            if ($p >= $matchPercent) return TRUE;
       }
    }

    foreach ($showEpisodes as $Log) {
        if (preg_match('/^INFO/', $Log)) continue;
        $Log = rtrim($Log);
        $data = explode("\t", $Log);
        $dat = preg_replace('/\([1-9]\)/', '', $data[2]);
        $dat = trim($dat);
        $data[4] = preg_replace('/<.+?>/', '', $data[4]);
        $datalc = strtolower($dat);
        $datalc = preg_replace('/[^0-9a-z ]+/i', '', $datalc);
        $datalc = preg_replace('/[^\w\d\s]+­/i', '', $datalc);
        $datalc = preg_replace('/(?: and | the | i | or | of |the | a | in )/i', '', $datalc);
        $datalc = preg_replace('/\s+/', '', $datalc);
        $datalc = preg_replace('/[\/\;]/', '', $datalc);

        $classes = "";

        // Check for date matches first and then subtitle.  I do this since some
        // episodes have bogus subtitles or no subtitle. 
        if ($prevMatch = in_array("$data[1]", $recDate)) {
        }else{
            $prevMatch = close_match("$datalc", $recEpisodes, $matchPercent);
        }
        if ($schedMatch = in_array("$data[1]", $schedDate)) {
        }else{
            $schedMatch = close_match("$datalc", $schedEpisodes, $matchPercent);
        }

        if ($schedMatch) {
            $classes .= " list_separator";
            $boxCheck = "unchecked";
        } elseif ($prevMatch) {
            if ($allEpisodes != "all") {
                $boxCheck = "unchecked";
                continue;
            }
            $classes .= " deactivated";
            $boxCheck = "unchecked";
        } else {
            if ($_SESSION['episodes']['allepisodes'] == "sched") continue;
            $classes .= " scheduled";
            $boxCheck = "checked";
        }
        ?>

        <?php

        if ((preg_match('/^Season/', $data[0])) && (!$special)) {
            $special = 1;
        ?>
            <tr class="menu" align="left">
              <td>Special Episodes</td>
            </tr> 
            <tr class="menu" align="left">
              <td>Select</td>
              <td>Episode Number</td>
              <td>Original Airdate</td>
              <td>Subtitle</td>
              <td>Synopsis</td>
              <td>Status</td>
            </tr>
       <?php
       }

       ?>

            <tr class="<?php echo $classes ?>" align="left">
              <td>
                <input type="checkbox" <?php echo $boxCheck?> name="f[]" value="<?php echo htmlspecialchars($data[2])?>">
              </td>
     
        <td>
          <?php echo htmlspecialchars($data[0])?>
        </td>

        <td>
          <?php echo htmlspecialchars($data[1])?>
        </td>
 
        <?php
        if ($data[3] != "") {
        ?>
            <td>
              <a href=<?php echo $data[3]?>><?php echo htmlspecialchars($data[2])?></a>
            </td>

        <?php
        } else {
        ?>
            <td>
              <?php echo htmlspecialchars($data[2])?>
            </td>

        <?php
        }
        ?>

        <td width="60%">
          <?php echo htmlspecialchars($data[4])?>
        </td>

        <td class="<?php echo $classes?>">
          <?php if ($prevMatch) echo "Previously Recorded"?>
          <?php if ($schedMatch) echo "Scheduled to Record"?>
          <?php if (!$prevMatch && !$schedMatch) echo "Not Recorded"?>
        </td>
        </tr></tr>

<?php
    } 
$_SESSION['episodes']['allepisodes'] = "all";
?>

    <tr class="menu">
      <td>
        <input type="button" value="Toggle" onClick="my_select(<?php echo "$toggleSelect" ?>);">
      </td>
      <td colspan="6" align="center">
        <input type="hidden" value="<?php echo "$showTitle"?>" name="title">
        <input type="submit" value="Create tvwish list" name="submit" id="submit">
      </td>
    </tr>
  </table>	
</form>

<?php
}

if (isset($_SESSION['episodes']['title'])) {
?>

    <table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
    <tr class="menu">
      <td><?php echo t('Title')?></a></td>
      <td><?php echo t('Subtitle')?></a></td> 
      <td><?php echo t('Date Recorded')?></a></td>
      <td><?php echo t('Programid')?></a></td>
      <td><?php echo t('Synopsis')?></a></td>
      <?php/*<td><?php echo t('Delete')?></td>*/?>
    </tr>

    <?php

    $row = 0;

    foreach ($All_Shows as $show) {
        list($startdate, $time) = explode(" ", $show->chanid);
    ?>
        <tr class="scheduled">
          <td><?php echo $show->title; ?></td>
          <td><?php echo $show->subtitle ?></td>
          <td><?php echo $startdate ?></td>
          <td><?php echo $show->category ?></td>
          <td><?php echo $show->description ?></td>

          <?php
          /*
          // I'm temporarily disabling this since I don't think deleting a recording
          // from the oldrecorded is the only table that needs to be updated
          <td class="x-commands commands"><a id="delete_<?php echo $row?>" href="episode/episodes/?delete=yes&category=<?php echo urlencode($show->category)?>&title=<?php echo urlencode($show->title)?>" title="<?php echo t('Delete this episode') ?>"><?php echo t('Delete') ?></a></td>
          */
          ?>
 
        </tr>
    <?php
        $row++;
    }
    ?>

    </table>

<?php
}

// Print the page footer
require 'modules/_shared/tmpl/'.tmpl.'/footer.php';
?>
