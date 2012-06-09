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

// Set the desired page title
    $page_title = 'MythWeb - '.t('TV Shows');

// Headers from mythweb
    $headers[] = '<link rel="stylesheet" type="text/css"      href="'.skin_url.'/tv_upcoming.css">';

// Print the page header
    require 'modules/_shared/tmpl/'.tmpl.'/header.php';
?>

<style type="text/css">
td.off {
}

td.on {
    background: green;
    color: yellow;
    text-decoration: underline;
}

td.x-active {
    padding:            .35em .5em;
    border-left:        1px solid #304943;
    height:             2em;
    background-color:   #485;
}
</style>

<script>
function changeCell(td) {
    td.className = td.className == "on" ? "off" : "on";
}
</script>

<table width="100%" border="0" cellpadding="4" cellspacing="0" class="list small">
<tr align="center">
<?php
if ($_SESSION['show']['state'] != "recorded") {
?>
  <td>
    Total Shows:&nbsp;&nbsp;<?php echo "$allCount"?>&nbsp;&nbsp;
    Current Shows:&nbsp;&nbsp;<?php echo "$currentCount"?>
  </td>
<?php
} else {
?>
  <td>
    Total Shows:&nbsp;&nbsp;<?php echo "$recordedCount"?>
  </td>
<?php
}
?>
</tr>
<tr align="center">
  <td>
    <a href="http://code.google.com/p/mythepisode/" target="_blank">
      <?php echo t('Mythepisode') ?>
    </a>
    &nbsp;&nbsp; | &nbsp;&nbsp;
    <a onclick="ajax_add_request()" href="episode/show?state=update">
      <?php echo t('Update Show Listing') ?>
    </a>
  </td>
</tr>
</table>

<table id="display_options" class="commandbox commands" border="0" cellspacing="0" cellpadding="0">
<tr>
  <td class="x-title"><?php echo t('Display') ?>:</td>
  <td class="<?php echo ($_SESSION['show']['state'] == "all")?("x-active"):("x-check") ?>">
    <a onclick="ajax_add_request()" href="episode/show?state=all">
      <?php echo t('All TV Shows') ?>
  </td>
  <td class="<?php echo ($_SESSION['show']['state'] == "current")?("x-active"):("x-check") ?>">
    <a onclick="ajax_add_request()" href="episode/show?state=current">
      <?php echo t('Current TV Shows') ?>
  </td>
  <td class="<?php echo ($_SESSION['show']['state'] == "recorded")?("x-active"):("x-check") ?>">
    <a onclick="ajax_add_request()" href="episode/show?state=recorded">
      <?php echo t('Recorded TV Shows') ?>
  </td>
  <td class="x-check">
    <a onclick="ajax_add_request()" href="episode/previous_recordings">
      <?php echo t('Previous Recordings') ?>
  </td>
  <?php 
  if(!$tvwishHide) {
  ?>
  <td class="x-check">
    <a onclick="ajax_add_request()" href="episode/tvwish_list">
      <?php echo t('TVwish') ?>
  </td>
  <?php 
  }
  ?>
</tr>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="0" class="list small">
<tr align="center">
  <td>
    <?php
    for ($alphacount = 65; $alphacount <= 90; $alphacount++)
        printf("<font size=3><a href=\"episode/show?state=$state#%s\" >%s</a></font> \n", chr($alphacount), chr($alphacount));
    ?>
  </td>
</tr>
</table>
<br>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
<?php
if ($_SESSION['show']['state'] != "recorded") {
    $a=0;
    $i=0;
    $j=1;

    // Go through list of shows from tvrage.com and place theme on the page
        foreach ($allShows as $Log) { 
            $Log = rtrim($Log);
            $Log = preg_replace('/<.+?>/', '', $Log);
            $data = explode("\t", $Log);
            $data[0] = ucfirst($data[0]);
            $firstChar = $data[0]{0}; // Get first character
            $datastr = str_replace(' ', '', strtolower($data[1]));
            if ($state == "all") {
                $j=0;
            }

            if ($data[3] >= $j) {
                if ($firstChar != $fc1) {
                    if (!preg_match('/[^0-9]/', $firstChar)) {
                        if ($a == 0) {
                            $a=1;
?>			
                        <tr class="menu" align="center">
                          <td colspan="1">- <?php echo "0-9"?> -</td>

                    <?php
                        }
                    } else {
                        $a=0;                        
                        $i=0;
                    ?>

                        </tr><tr></tr><tr></tr>
                        <tr class="menu" align="center">
                          <td colspan="1">
                            <a name="<?php echo $firstChar?>">- <?php echo $firstChar?> -</a>
                          </td>
                        </tr>
                <?php
                    }
                }

                if ($i == 0) {
                ?>
                <tr class="settings" align="left">
                <?php
                    if (in_array("$datastr", $oldRecorded)) {
                ?>
				
                    <td bgcolor="green">
                      <a onclick="ajax_add_request()" href='episode/episodes/?showstr=<?php echo urlencode($data[1])?>&longshow=<?php echo urlencode($data[2])?>&showname=<?php echo urlencode($data[0])?>'><?php echo  htmlspecialchars($data[2])?></a>
                    </td>

                    <?php
                        } else {
                    ?>
                    <td>
                      <a onclick="ajax_add_request()" href='episode/episodes/?showstr=<?php echo urlencode($data[1])?>&longshow=<?php echo urlencode($data[2])?>&showname=<?php echo urlencode($data[0])?>'><?php echo htmlspecialchars($data[2])?></a>
                    </td>

                    <?php
                        }
                        $i = $i + 1;
                        $fc1=$firstChar;
                
                    } else {
                        if (in_array("$datastr", $oldRecorded)) {
                    ?>
                        <td bgcolor="green">
                          <a onclick="ajax_add_request()" href='episode/episodes/?showstr=<?php echo urlencode($data[1])?>&longshow=<?php echo urlencode($data[2])?>&showname=<?php echo urlencode($data[0])?>'><?php echo htmlspecialchars($data[2])?></a>
                        </td>

                    <?php
                        } else {
                    ?>
                    
                        <td>
                          <a onclick="ajax_add_request()" href='episode/episodes/?showstr=<?php echo urlencode($data[1])?>&longshow=<?php echo urlencode($data[2])?>&showname=<?php echo $data[0]?>&allepisodes=<?php echo all?>'><?php echo htmlspecialchars($data[2])?></a>
                        </td>

                    <?php
                        }
                        $i = $i + 1;
                        $fc1=$firstChar;
                    } 
                    if ($i == 5) {
                        $i=0;
                    ?>
                </tr>

                <?php
                    }
                }
            }
    }
                ?>    

<?php
    if ($_SESSION['show']['state'] == "recorded") {
        $i=0;
        $stillNums = false;

    // Go through list of shows from tvrage.com and place theme on the page
        foreach ($oldRecorded as $show) {
            $data = $recordedShows[$show];
            if (! $data ) {
                continue;
            }
            static $excludes = '/^(?i)(an?|the)\s+/'; // Add excluded words here
            $firstChar = strtoupper(substr(preg_replace($excludes, '', $data[1]),0,1));
            if ($firstChar != $fc1) {
                if (!preg_match('/[^0-9\']/', $firstChar)) {
                    if (!$stillNums) {
                        $stillNums = true;
?>
<tr class="menu" align="center">
  <td colspan="1">- <?php echo "0-9"?> -</td>

                <?php
                    }
                } else {
                    $i=0;
                ?>

</tr><tr></tr><tr></tr>
<tr class="menu" align="center">
  <td colspan="1">
       <a name="<?php echo $firstChar?>">- <?php echo $firstChar?> -</a>
    </td>
</tr>
            <?php
                }
            }

            if ($i == 0) {
            ?>
<tr class="settings" align="left">
        <?php
            }
        ?>

   <td onmouseover="changeCell(this)" onmouseout="changeCell(this)"><a onclick="ajax_add_request()" href="episode/episodes/?showstr=<?php echo urlencode($data[1])?>&longshow=<?php echo urlencode($data[2])?>&showname=<?php echo urlencode($data[0])?>"><?php echo htmlspecialchars($data[2])?>
   </td>

        <?php
            $i = $i + 1;
            $fc1=$firstChar;
            if ($i == 5) {
                $i=0;
       ?>
</tr>
<?php
                    }
            }
    }
?>


</table>

<?php
// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';
?>
