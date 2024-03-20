<?php

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
    && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()
) {
    // Add Backend-Module
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon(
        'module-cachemgm-backend-module',
        SvgIconProvider::class,
        [
            'source' => 'EXT:cachemgm/ext_icon.svg'
        ]
    );

    ExtensionUtility::registerModule(
        'cachemgm',
        'tools',
        'cachemgm',
        '',
        [
            Aoe\Cachemgm\Controller\BackendModuleController::class => 'index,detail,flush',
        ],
        [
            'access' => 'admin',
            'icon' => 'EXT:' . 'cachemgm' . '/Resources/Public/Icons/Extension.svg',
            'labels' => 'LLL:EXT:' . 'cachemgm' . '/Resources/Private/BackendModule/Language/locallang.xlf',
        ]
    );
}
