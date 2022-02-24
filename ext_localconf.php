<?php

use Aoe\Cachemgm\Hooks\TypoScriptFrontendHook;

if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE=='FE')	{
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_cachemgm'] =
        TypoScriptFrontendHook::class . '->fe_headerNoCache';
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['cachemgm'] = [
    'key' => 'tx_cachemgm_recache',
    'value' => 'Re-cache pages'
];
