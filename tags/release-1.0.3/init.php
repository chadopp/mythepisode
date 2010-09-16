<?php
/**
 * Initialization routines for the episode module
 *
 * @url         $URL$
 * @date        $Date$
 * @version     $Revision$
 * @author      $Author$
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
