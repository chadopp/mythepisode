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

<table id="display_options" width="100%" border="0" cellpadding="4" cellspacing="2" class="commandbox commands">
<tr class="menu">
   <td>Active TVwish lists</td>
</tr>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="commandbox commands">    
<?php
    foreach ($activatedShow as $activeShow) {
        if (eregi("^(Include)", $activeShow)) {
            $activeShow       = ltrim($activeShow, "Include: $tvwishep/");
            $activeShow       = trim($activeShow);
            $masterContent    = file("$tvwishep/$activeShow");
            $masterSeriesName = explode(": ", $masterContent[0]);

?>
<tr class="deactivated" align="left">
   <td width=6%><a href="episode/tvwish_list?wishstr=deactivate&setting=<?php echo urlencode($activeShow) ?>"><?php echo Deactivate?></a></td>
   <td width=4% align="left"><a href="episode/tvwish_list?wishstr=delete&setting=<?php echo urlencode($activeShow) ?>"><?php echo Delete?></a></td>
   <td><?php echo "$masterSeriesName[1]" ?></td>   
</tr>

    <?php
        }
    }
    ?>
</table>

<table id="display_options" width="100%" border="0" cellpadding="4" cellspacing="2" class="commandbox commands">
<tr class="menu">
   <td>Available TVwish lists</td>
</tr>
</table>

<table width="100%" border="0" cellpadding="4" cellspacing="2" class="commandbox commands">
<?php
    foreach ($wishFiles as $deactiveShow) {
        $deactiveShowName  = explode("::", $deactiveShow);
        if (!in_array($deactiveShowName[0], $deactivatedShow)) {
?>

<tr class="deactivated" align="left">
   <td width=6%><a href="episode/tvwish_list?wishstr=activate&setting=<?php echo urlencode("Include: $tvwishep/$deactiveShowName[0]") ?>"><?php echo Activate?></a></td>
 <td width=4% align="left"><a href="episode/tvwish_list?wishstr=delete&setting=<?php echo urlencode("$deactiveShowName[0]") ?>"><?php echo Delete?></a></td>
   <td><?php echo "$deactiveShowName[1]" ?></td>
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
