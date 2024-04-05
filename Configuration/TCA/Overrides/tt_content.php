<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

// Note: Not using \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin() here on purpose.
// We let EXT:container register the CType.
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class)->configureContainer(
    (
        new \B13\Container\Tca\ContainerConfiguration(
            'contentpassword_container', // CType
            'Passwortschutz', // label
            '', // description
            [
                [
                    ['name' => 'Passwortgeschützter Inhalt', 'colPos' => 200]
                ]
            ] // grid configuration
        )
    )
    // override default configurations
    ->setSaveAndCloseInNewContentElementWizard(false)
);

// override default settings
$GLOBALS['TCA']['tt_content']['types']['contentpassword_container']['showitem'] = '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;headers,
                pi_flexform,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:content_password/Configuration/FlexForms/ContentPassword.xml',
    'contentpassword_container',
);
