<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Password protection for content',
    'description' => '',
    'category' => 'plugin',
    'author' => 'Benjamin Franzke',
    'author_email' => 'bfr@qbus.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.1',
    'constraints' => array(
        'depends' => array(
            'typo3' => '11.5.0-11.5.99',
            'container' => '1.0.0-2.99.99',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);
