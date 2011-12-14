<?php
/**
 * Welcome page description of the episode module.
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Open with a div and an image
    echo '<div id="info_episode" class="hidden">',
         '<img src="', skin_url, '/img/tv.png" class="module_icon" alt="">',

// Print a basic overview of what this module does
         t('welcome: TV Episodes'),

// Next, print a list of possible subsectons
        '<ul>';
    foreach (Modules::getModuleProperty('episode', 'links') as $link => $name) {
        echo ' <li><a href="', root_url, Modules::getModuleProperty('episode', 'path'), '/', $link, '">', html_entities($name), "</a></li>\n";
    }
    echo '</ul>',

// Close the div
         "</div>\n";
