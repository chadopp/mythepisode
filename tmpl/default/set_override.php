<?php
/**
 * Configure Mythepisode settings info
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      Author: Chris Kapp
 * @license     GPL
 *
/**/
?>

<form class="form" method="post" action="<?php echo form_action ?>">

<table border="0" cellspacing="0" cellpadding="0">
<tr class="menu" align="center"> 
  <td>MythTV Show</td> 
  <td>tvrage.com Show</td> 
  <td>Delete</td> 
</tr>

<?php
foreach ($overrideFile as $overrideShow) {
    list($mythName,$rageName) = explode(":::", "$overrideShow");
    $rageName  = trim($rageName);
    $mythName  = trim($mythName);
    $mythTitle = explode("---", "$mythName");
    // Determine each new show title and add it to oldRecorded array
    natsort($mythTitle);
    foreach ($mythTitle as $tempTitle) {
        echo "<tr class=\"settings\">";
        echo "<td align=\"right\">".$tempTitle.":&nbsp;</td> ";
        echo "<td><input type=\"text\" size=\"64\" name=\"settings[".$tempTitle."]\" value=\"".$rageName."\"></td>";
        echo "<td><input type=\"checkbox\" name=\"delete[".$tempTitle."]\" value=\"".$rageName."\"></td>";
        echo "</tr>\n";
    }
}

foreach ($oldRecorded as $oldShow) {
    echo "<tr class=\"settings\">";
    echo "<td align=\"right\">".$oldShow.":&nbsp;</td> ";
    echo "<td><input type=\"text\" size=\"64\" name=\"settings[".$oldShow."]\" value=\"";
    echo "\"></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>\n";
}
?>


<tr>
  <td align="center"><input type="reset" value="<?php echo t('Reset') ?>"></td>
  <td align="center"><input type="submit" name="save" value="<?php echo t('Save') ?>"></td>
</tr>
</table>

</form>
