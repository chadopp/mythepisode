<?php
/**
 * previous recording functions
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/


// Data display sorting function for previous recordings
    function get_sort_link_with_parms($field, $string) {
        $link = get_sort_link($field,$string);
        $pos = strpos($link, '?') + 1;
        return substr($link,0,$pos).'&'.substr($link,$pos);;
    }

// Sorting Functions
    function cmp($a, $b) {
        static $excludes = '/^(?i)(an?|the)\s+/'; // Add excluded words here
        return strcasecmp(
            preg_replace($excludes, '', ((is_array($a))?$a[0]:$a)),
            preg_replace($excludes, '', ((is_array($b))?$b[0]:$b))
        );
    }
