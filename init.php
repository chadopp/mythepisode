<?php
/**
 * Initialization routines for the episode module
 *
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author: coppliger
 * @license     GPL
 *
/**/

// If mythepisode is enabled, add it to the list.
    if (tmpl == 'default') {
        $Modules['episode']  = array('path'        => 'episode/show?state=recorded',
                                   'sort'        => 3,
                                   'name'        => t('TV Episodes'),
                                   'description' => t('')
                                  );
    };
