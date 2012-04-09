<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='FE')	{
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['headerNoCache']['tx_cachemgm'] = 'EXT:cachemgm/class.tx_cachemgm_lib.php:&tx_cachemgm_lib->fe_headerNoCache';
}

	// Register with "crawler" extension:
$TYPO3_CONF_VARS['EXTCONF']['crawler']['procInstructions']['tx_cachemgm_recache'] = 'Re-cache pages';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['cachemgm_log'] 			= array('EXT:cachemgm/cli/cachemgm_log.php','_CLI_lowlevel');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']['cachemgm_top'] 			= array('EXT:cachemgm/cli/cachemgm_top.php','_CLI_lowlevel');


?>