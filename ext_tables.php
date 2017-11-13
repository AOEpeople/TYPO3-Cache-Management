<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	// Add Backend-Module
	$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
	$iconRegistry->registerIcon(
		'module-cachemgm-backend-module',
		\TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
		[
			//'name' => 'database'
			'source' => 'EXT:cachemgm/Resources/Public/BackendModule/ModuleIcon.gif',
		]
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'tools',
		'txcachemgmM1',
		'',
		'',
		[
			'routeTarget' => Tx_Cachemgm_Controller_BackendModuleController::class . '::mainAction',
			'name' => 'tools_txcachemgmM1',
			'access' => 'admin',
			'labels' => [
				'll_ref' => 'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang_mod.xlf'
			],
			'iconIdentifier' => 'module-cachemgm-backend-module'
		]
	);



		// Add Web>Info module (cache_pages et al.)
	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		'web_info',
		'tx_cachemgm_modfunc1',
		null,
		'LLL:EXT:cachemgm/locallang.xlf:moduleFunction.tx_cachemgm_modfunc1'
	);
}
