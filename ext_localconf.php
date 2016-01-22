<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/TSConfig/ContentPassword.pagets">');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Qbus.' . $_EXTKEY,
    'ContentPassword',
    array(
        'ContentPassword' => 'main, unlock',

    ),
    // non-cacheable actions
    array(
        'ContentPassword' => 'unlock',
    )
);
