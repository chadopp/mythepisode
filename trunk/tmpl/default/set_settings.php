<?php
/**
 * Configure Mythepisode settings info
 *
 * @url         $URL: $
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/
?>

<form class="form" method="post" action="<?php echo form_action ?>">

<table border="0" cellspacing="0" cellpadding="0">
<tr>
  <td colspan="2"><?php echo t('General Settings') ?>:</th>
</tr>
<tr>
  <th><?php echo t('Default page view') ?></label>:</th>
  <td>
    <select name="default_page">
    <?php
    foreach(array('recorded', 'all', 'current') as $value) {
        echo '<option value="'.$value.'" ';
        if ($value == $config['defaultView'])
            echo ' SELECTED ';
        if ($value == 'null')
            $value = t('recorded');
        echo '>'.$value.'</option>';
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <th><?php echo t('Display tvwish options') ?></label>:</th>
  <td>
    <select name="display_tvwish">
    <?php
    if ($config['tvwishHide'] == '0')
        $config['tvwishHide'] = 'yes';
    else
        $config['tvwishHide'] = 'no';
    foreach(array('yes', 'no') as $value) {
        echo '<option value="'.$value.'" ';
        if ($value == $config['tvwishHide'])
            echo ' SELECTED ';
        if ($value == 'null')
            $value = t('yes');
        echo '>'.$value.'</option>';
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <th><?php echo t('Episode matching accuracy (%)') ?></label>:</th>
  <td>
    <select name="episode_match">
    <?php
    foreach(range(75,100) as $value) {
        echo '<option value="'.$value.'" ';
        if ($value == $config['matchPercent'])
            echo ' SELECTED ';
        if ($value == 'null')
            $value = t('85');
        echo '>'.$value.'</option>';
    }
    ?>
    </select>
  </td>
</tr>
<tr class="x-sep">
  <th><?php echo t('Update episode info if older than (days)') ?></label>:</th>
  <td>
    <select name="episode_update">
    <?php
    foreach(range(3,14) as $value) {
        echo '<option value="'.$value.'" ';
        if ($value ==  $config['maxFileAge'])
            echo ' SELECTED ';
        if ($value == 'null')
            $value = t('7');
        echo '>'.$value.'</option>';
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <td align="center"><input type="reset" value="<?php echo t('Reset') ?>"></td>
  <?php/*<td align="center"><input type="submit" name="save" value="<?php echo t('Save') ?>"></td>*/?>
</tr>
</table>

</form>
