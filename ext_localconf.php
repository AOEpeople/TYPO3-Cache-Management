<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE=='FE')	{
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_cachemgm'] =
        \Aoe\Cachemgm\Hooks\TypoScriptFrontendHook::class . '->fe_headerNoCache';
}
$isVersion8OrLower = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9000000;

// Register with "crawler" extension:
if ($isVersion8OrLower) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_cachemgm_recache'] = 'Re-cache pages';
} else {
    // Needed for the Crawler 9.0.0 (dev-typo3v9)
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['cachemgm'] = [
        'key' => 'tx_cachemgm_recache',
        'value' => 'Re-cache pages'
    ];
}
