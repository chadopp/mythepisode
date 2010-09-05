<?php
/**
 * Welcome page description of the episode module.
 *
 * @date        $Date: 2010-08-01 $
 * @version     $Revision: 1.0 $
 * @author      $Author: coppliger $
 * @license     $GPL $
 *
/**/

// Open with a div and an image
    echo '<div id="info_episode" class="hidden">',
         '<img src="', skin_url, '/img/tv.png" class="module_icon" alt="">',

// Print a basic overview of what this module does
         t('welcome: TV Episodes'),

// Close the div
         "</div>\n";
