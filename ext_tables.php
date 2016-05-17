<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txcachemgmM1','',TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod/');

		// Add Web>Info module (cache_pages et al.)
	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_info',
		'tx_cachemgm_modfunc1',
		null,
		'LLL:EXT:cachemgm/locallang_db.php:moduleFunction.tx_cachemgm_modfunc1'
	);
}
?>