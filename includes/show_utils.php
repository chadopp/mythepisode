<?php
/**
 * show functions
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
 * @license     GPL
 *
/**/

// Sorting Functions
    function cmp_shows($a, $b) {
        static $excludes = '/^(?i)(an?|the)\s+/'; // Add excluded words here
        return strcasecmp(
            preg_replace($excludes, '', $a),
            preg_replace($excludes, '', $b)
        );
    }

