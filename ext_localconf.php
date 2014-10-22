<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

//$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dbal_utility']);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Database\\DatabaseConnection'] = array(
    'className' => 'Lightwerk\\DbalUtility\\TYPO3\\Database\\DatabaseConnection',
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Database\\PreparedStatement'] = array(
    'className' => 'Lightwerk\\DbalUtility\\TYPO3\\Database\\PreparedStatement',
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['isOutputting'][] = 'Lightwerk\\DbalUtility\\TYPO3\\OutputHttpHeader->addHttpHeaders';