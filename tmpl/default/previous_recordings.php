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

// Set the desired page title
    $page_title = 'MythWeb - '.t('TV Previously Recorded');

// Headers from mythweb
    $headers[] = '<link rel="stylesheet" type="text/css"      href="'.skin_url.'/tv_upcoming.css">';

// Print the page header
    require 'modules/_shared/tmpl/'.tmpl.'/header.php';

// Load includes
    require_once 'includes/previous_utils.php';

?>

<style type="text/css">
td.x-active {
    padding:            .35em .5em;
    border-left:        1px solid #304943;
    height:             2em;
    background-color:   #485;
}
</style>

<?php require 'modules/episode/tmpl/'.tmpl.'/menu.php'; ?>

<p>
<form id="program_titles" action="episode/previous_recordings" method="get">
<table class="command command_border_l command_border_t command_border_b command_border_r" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
    <td><?php echo t('Show Previous Recordings') ?>:</td>
    <td><select name="title" onchange="ajax_add_request(); $('program_titles').submit()">
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


<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
  <tr class="menu">
    <td><?php echo t('Title')?></a></td>
    <td><?php echo get_sort_link_with_parms('subtitle',t('Subtitle'))?></a></td>
    <td><?php echo get_sort_link_with_parms('programid',t('Programid'))?></a></td>
    <td><?php echo t('Synopsis')?></a></td>
  </tr>

<?php
foreach ($All_Shows as $prevrec) {
?>

  <tr class="deactivated">
    <td><?php echo $prevrec->title?></td>
    <td><?php echo $prevrec->subtitle?></td>
    <td><?php echo $prevrec->programid?></td>
    <td><?php echo $prevrec->description?></td>
    <td class="x-commands commands"><a onclick="ajax_add_request()" href="episode/previous_recordings?delete=yes&programid=<?php echo urlencode($prevrec->programid)?>"  title="<?php echo t('Delete this episode') ?>"><?php echo t('Delete') ?></a></td>
  </tr>

<?php
}
?>

</table>

<?php
// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';
?>
