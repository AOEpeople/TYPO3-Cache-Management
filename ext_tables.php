<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE') {
	// Add Backend-Module
	$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
	$iconRegistry->registerIcon(
		'module-cachemgm-backend-module',
		\TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
		[
			//'name' => 'database'
			'source' => 'EXT:cachemgm/ext_icon.svg',
		]
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
		'tools',
		'txcachemgmM1',
		'',
		'',
		[
			'routeTarget' => \Aoe\Cachemgm\Controller\BackendModuleController::class . '::mainAction',
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
        \Aoe\Cachemgm\Backend\BackendModule::class,
		null,
		'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:moduleFunction.tx_cachemgm_modfunc1'
	);
}
