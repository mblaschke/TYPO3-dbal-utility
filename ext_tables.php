<?php
defined('TYPO3_MODE') or exit();

$extPath    = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY);


// ############################################################################
// Backend
// ############################################################################

if (TYPO3_MODE == 'BE') {

    // ####################################################
    // Module category "WEB"
    // ####################################################

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Lightwerk.' . $_EXTKEY,
        'tools',
        'dbal_utility',
        '', # Position
        array('Backend' => 'main,show'), # Controller array
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Backend/Icons/ModuleDbal.png',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.backend.xml',
        )
    );

}
