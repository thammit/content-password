<?php

declare(strict_types=1);

use Qbus\ContentPassword\Controller\ContentPasswordController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'ContentPassword',
    'Container',
    [ContentPasswordController::class => 'main, unlock'],
    [ContentPasswordController::class => 'unlock'],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
