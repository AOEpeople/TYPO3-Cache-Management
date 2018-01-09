<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE=='FE')	{
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_cachemgm'] =
        \Aoe\Cachemgm\Hook\TypoScriptFrontendHook::class . '->fe_headerNoCache';
}

	// Register with "crawler" extension:
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['crawler']['procInstructions']['tx_cachemgm_recache'] = 'Re-cache pages';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['cachemgm_log'] = array('EXT:cachemgm/cli/cachemgm_log.php','_CLI_lowlevel');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['cachemgm_top'] = array('EXT:cachemgm/cli/cachemgm_top.php','_CLI_lowlevel');
