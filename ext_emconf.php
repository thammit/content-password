<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Password protection for content',
    'description' => '',
    'category' => 'plugin',
    'author' => 'Benjamin Franzke',
    'author_email' => 'bfr@qbus.de',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.2.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2',
            'gridelements' => '3.0',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);
