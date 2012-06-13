<?php
/**
 * episode common functions
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Resize the jpg displayed for series info based off of user preferences
    function imageResize($width, $height, $target) {

    // Takes the larger size of the width and height and applies the
    // formula accordingly...this is so this script will work
    // dynamically with any size image

        if ($width > $height)
            $percentage = ($target / $width);
        else
            $percentage = ($target / $height);

    // Gets the new value and applies the percentage, then rounds the value
        $width  = round($width * $percentage);
        $height = round($height * $percentage);

    // Returns the new sizes in html image tag format...this is so you
    // can plug this function inside an image tag and just get the

        return "width=\"$width\" height=\"$height\"";

    }

// Sort recorded episodes
    function get_sort_link_recorded($field, $string, $parms) {
        $link = get_sort_link($field,$string);
        $pos = strpos($link, '?') + 1;
        return substr($link,0,$pos).$parms.'&'.substr($link,$pos);
    }
