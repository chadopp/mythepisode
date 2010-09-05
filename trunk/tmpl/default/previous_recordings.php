<?php
/**
 * previous recordings
 *
 * @date        $Date: 2010-08-01 $
 * @version     $Revision: 1.0 $
 * @author      $Author: coppliger $
 * @license     $GPL $
 *
/**/

// Set the desired page title
$page_title = 'MythWeb - '.t('TV Previously Recorded');

// Print the page header
require 'modules/_shared/tmpl/'.tmpl.'/header.php';

global $All_Shows, $Total_Programs;

?>

<p>
<form id="program_titles" action="episode/previous_recordings" method="get">
<table class="command command_border_l command_border_t command_border_b command_border_r" border="0" cellspacing="0" cellpadding="4" align="center">
<tr>
    <td><?php echo t('Show Previous Recordings') ?>:</td>
    <td><select name="title" onchange="$('program_titles').submit()">
        <?php
        global $Program_Titles;
        foreach($Program_Titles as $title => $count) {
            echo '<option value="'.htmlspecialchars($title).'"';
            if ($_GET['title'] == $title)
                echo ' SELECTED';
            echo '>'.htmlentities($title, ENT_COMPAT, 'UTF-8')
                .($count > 1 ? ' ('.tn('$1 episode', '$1 episodes', $count).')' : "")
                .'</option>';
        }
        ?>
    </select></td>
    <td><noscript><input type="submit" value="<?php echo t('Go') ?>"></noscript></td>
</tr>
</table>
</form>
</p>


<?php
// Setup for grouping by various sort orders
$group_field = $_GET['sortby'];
if ( ! (($group_field == "title") || ($group_field == "subtitle") || ($group_field == "description") || ($group_field == "programid")) ) {
    $group_field = "";
}
?>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
<tr class="menu">
    <?php
    if ($group_field != "") {
        echo "\t<td class=\"list\">&nbsp;</td>\n";
    }
    ?>
    <td><a href="episode/previous_recordings?sortby=title"><?php echo  t('title')?></a></td>
    <td><a href="episode/previous_recordings?sortby=subtitle"><?php echo t('subtitle')?></a></td>
    <td><a href="episode/previous_recordings?sortby=category"><?php echo t('programid')?></a></td>

    <?php
    if ($_SESSION['previous_recorded_descunder'] != "on")
        echo "\t<td><a href=\"episode/previous_recordings?sortby=description\">".t('description')."</a></td>\n";
    ?>
</tr><?php

    $row = 0;

    $prev_group="";
    $cur_group="";

    foreach ($All_Shows as $show) {

    if ($group_field == "title")
        $cur_group = $show->title;

    ?><tr class="scheduled">
    <?php
    if ($group_field != "")
        if ($_SESSION['previous_recorded_descunder'] != "on")
            echo "\t<td class=\"list\">&nbsp;</td>\n";
        else
            echo "\t<td class=\"list\" rowspan=\"2\">&nbsp;</td>\n";
    ?>
    <td><?php echo $show->title; ?></td>
    <td><?php echo $show->subtitle?></td>
    <td><?php echo $show->category?></td>
    <?php
    if ($_SESSION['previous_recorded_descunder'] != "on")
        echo("<td>".$show->description."</td>");
    ?>
    <td class="x-commands commands"><a id="delete_<?php echo $row?>" href="episode/previous_recordings?delete=yes&category=<?php echo urlencode($show->category)?>"  title="<?php echo t('Delete this episode') ?>"><?php echo t('Delete') ?></a></td>
    </td>

    </tr><?php
        if ($_SESSION['previous_recorded_descunder'] == "on")
            echo("<tr class=\"recorded\">\n\t<td colspan=\"7\">".$show->description."</td>\n</tr>");
        $prev_group = $cur_group;
        $row++;
    }
    ?>

</table>
</form>

<?php
// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';
?>
