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
        $rootDir       = getcwd();
        $dataDir       = "$rootDir/data";
        $epDir         = "$dataDir/episode";
        $configFile    = "$epDir/config.ini";
        $tvwishHide    = 0;
        $links         = "";
        if (file_exists($configFile)) {
            $config     = parse_ini_file($configFile, 1);
            $tvwishHide = (empty($config['tvwishHide'])) ? '0' : $config['tvwishHide'];
        }
        if ($tvwishHide) {
            $links = array( 'show'                => t('TV Shows'),
                            'previous_recordings' => t('Previous Recordings')
                           );
        } else {
            $links = array( 'show'                => t('TV Shows'),
                            'previous_recordings' => t('Previous Recordings'),
                            'tvwish_list'         => t('TVwish')
                           );
        }
		
        $Modules['episode'] = array('path'  => 'episode',
                                    'sort'  => 3,
                                    'name'  => t('TV Episodes'),
                                    'links' => $links
                                    );
        $Settings['episode'] = array('name'    => t('Mythepisode'),
                                   'choices' => array('settings' => t('Settings'),
                                                     ),
                                   'default' => 'settings',
                                  );

    };

