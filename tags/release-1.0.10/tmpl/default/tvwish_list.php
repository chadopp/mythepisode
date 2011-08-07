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

// Set the desired page title
    $page_title = 'MythWeb - '.t('TV Wish');

// Headers from mythweb
    $headers[] = '<link rel="stylesheet" type="text/css"      href="'.skin_url.'/tv_upcoming.css">';

// Print the page header
    require 'modules/_shared/tmpl/'.tmpl.'/header.php';

    global $activatedShow, $deactivatedShow, $wishFiles;
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

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
<tr class="menu" align="center">
   <td>Master TVwish listing file</td>
</tr>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">    
<?php
    foreach ($activatedShow as $activeShow) {
        if (eregi("^(Include)", $activeShow)) {
            $activeShow = ltrim($activeShow, "Include: $tvwishep/");
?>
<tr class="settings" align="left">
   <td width=15%><a href="episode/tvwish_list?wishstr=deactivate&setting=<?php echo $activeShow ?>"><?php echo Deactivate?></a></td>
   <td><?php echo $activeShow ?></td>   
</tr>

    <?php
        }
    }
    ?>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
<tr class="menu" align="center">
   <td>Available TVwish lists</td>
</tr>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="list small">
<?php
    foreach ($wishFiles as $deactiveShow) {
        if (!in_array($deactiveShow, $deactivatedShow)) {
?>

<tr class="settings" align="left">
   <td width=15%><a href="episode/tvwish_list?wishstr=activate&setting=<?php echo "Include: $tvwishep/$deactiveShow" ?>"><?php echo Activate?></a></td>
   <td><?php echo "$deactiveShow" ?></td>
</tr>

    <?php
        }
    }
    ?>

</table>

<?php
// Print the page footer
    require 'modules/_shared/tmpl/'.tmpl.'/footer.php';
?>
