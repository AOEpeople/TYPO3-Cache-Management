<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	t3lib_extMgm::addModule('tools','txcachemgmM1','',t3lib_extMgm::extPath($_EXTKEY).'mod/');
	
		// Add Web>Info module (cache_pages et al.)
	t3lib_extMgm::insertModuleFunction(
		'web_info',
		'tx_cachemgm_modfunc1',
		t3lib_extMgm::extPath($_EXTKEY).'modfunc1/class.tx_cachemgm_modfunc1.php',
		'LLL:EXT:cachemgm/locallang_db.php:moduleFunction.tx_cachemgm_modfunc1'
	);
}
?>