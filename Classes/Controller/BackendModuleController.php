<?php

declare(strict_types=1);

namespace Aoe\Cachemgm\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Aoe\Cachemgm\Domain\Repository\CacheTableRepository;
use Aoe\Cachemgm\Utility\CacheUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Fluid\View\TemplatePaths;

class BackendModuleController extends ActionController
{
    /**
     * @var ModuleTemplateFactory
     */
    public $moduleTemplateFactory;

    /**
     * BackendTemplateContainer
     */
    protected $view;

    /**
     * @var CacheManager
     */
    private readonly object $cacheManager;

    /**
     * @var LanguageService
     */
    private $languageService;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
    ) {
        $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $this->languageService = $GLOBALS['LANG'];
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->view->assignMultiple([
            'cacheConfigurations' => $this->buildCacheConfigurationArray(),
            'action_confirm_flush_message' => $this->languageService->sL(
                'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.action_confirm_flush'
            ),
        ]);

        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function detailAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        try {
            $cacheId = $this->request->getArgument('cacheId');
        } catch (NoSuchArgumentException) {
            $this->showFlashMessage($this->getNoCacheFoundMessage());
            return new ForwardResponse('index');
        }

        $cache = $this->cacheManager->getCache($cacheId);
        $backend = $cache->getBackend();

        $propertiesArray = $this->getBackendCacheProperties($backend);
        $fileBackend = $this->getFileBackendInfo($backend);
        $cacheCount = $this->getCacheCount($backend);

        $this->view->assignMultiple(
            [
                'cacheId' => $cacheId,
                'cacheInformation' => [
                    'Frontend Classname' => $cache::class,
                    'Backend Classname' => $backend::class,
                ],
                'fileBackend' => $fileBackend,
                'cacheCount' => $cacheCount,
                'properties' => $propertiesArray,
                'overviewLink' => $this->getHref('BackendModule', 'index'),
            ]
        );

        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function flushAction(): ResponseInterface
    {
        try {
            $cacheId = $this->request->getArgument('cacheId');
        } catch (NoSuchArgumentException) {
            $this->showFlashMessage($this->getNoCacheFoundMessage());
            return new ForwardResponse('index');
        }

        $cache = $this->cacheManager->getCache($cacheId);
        $cache->flush();
        $this->showFlashMessage($this->getFlushCacheMessage($cacheId));
        return new ForwardResponse('index');
    }

    protected function initializeView(): void
    {
        if ($this->view instanceof \TYPO3\CMS\Fluid\View\StandaloneView) {
            $this->view->setLayoutRootPaths(['EXT:cachemgm/Resources/Private/Layouts']);
            $this->view->setPartialRootPaths(['EXT:cachemgm/Resources/Private/Partials']);
            $this->view->setTemplateRootPaths(['EXT:cachemgm/Resources/Private/Templates/BackendModule']);
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildCacheConfigurationArray(): array
    {
        $cacheConfigurations = [];

        foreach (CacheUtility::getAvailableCaches() as $cacheId) {
            $cacheConfigurations[] =
                [
                    'name' => $cacheId,
                    'type' => CacheUtility::getCacheType($cacheId),
                    'backend' => CacheUtility::getCacheBackendType($cacheId),
                    'options' => CacheUtility::getCacheOptions($cacheId),
                    'detailsUrl' => $this->getHref('BackendModule', 'detail', [
                        'cacheId' => $cacheId,
                    ]),
                    'flushUrl' => $this->getHref('BackendModule', 'flush', [
                        'cacheId' => $cacheId,
                    ]),
                ];
        }

        return $cacheConfigurations;
    }

    /**
     * Creates te URI for a backend action
     */
    private function getHref(string $controller, string $action, array $parameters = []): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->reset()
            ->uriFor($action, $parameters, $controller);
    }

    /**
     * @return array<string, mixed>
     */
    private function getBackendCacheProperties(BackendInterface $backend): array
    {
        $reflectionBackend = new \ReflectionObject($backend);
        $properties = $reflectionBackend->getProperties();
        $propertiesArray = [];
        foreach ($properties as $key => $value) {
            $properties[$key]->setAccessible(true);
            // check if element is an object and the property is valid
            if ($properties[$key]->isInitialized($backend)) {
                $value = $properties[$key]->getValue($backend);
                if (is_object($value)) {
                    $value = 'Object: ' . $value::class;
                }

                // remove elements that are not a string
                $propertiesArray[$properties[$key]->getName()] = is_string($value) ? $value : '';
            }
        }

        return $propertiesArray;
    }

    private function getFileBackendInfo(BackendInterface $backend): ?string
    {
        $fileBackend = null;
        if ($backend instanceof SimpleFileBackend) {
            $fileBackend = 'Cache Folder: ' . $backend->getCacheDirectory();
            if (!is_writable($backend->getCacheDirectory())) {
                $fileBackend .= '&nbsp;<span class="badge badge-danger">' .
                    $this->languageService->sL(
                        'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.warning.not_writeable'
                    )
                    . '</span>';
            }
        }

        return $fileBackend;
    }

    /**
     * @return array<string, mixed>
     */
    private function getCacheCount(BackendInterface $backend): array
    {
        $cacheTableRepository = GeneralUtility::makeInstance(CacheTableRepository::class);

        $cacheCount = [];
        if ($backend instanceof Typo3DatabaseBackend) {
            $cacheCount['Cache Table'] = $backend->getCacheTable();
            $cacheCount['Cache Entry Count'] = $cacheTableRepository->countRowsInTable($backend->getCacheTable());
            $cacheCount['Cache Tags Table'] = $backend->getTagsTable();
            $cacheCount['Cache Tags Entry Count'] = $cacheTableRepository->countRowsInTable($backend->getTagsTable());
        }

        return $cacheCount;
    }

    private function getFlushCacheMessage(string $cacheId): object
    {
        return GeneralUtility::makeInstance(
            FlashMessage::class,
            sprintf(
                $this->languageService->sL(
                    'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.flash.flush.success'
                ),
                $cacheId
            ),
            $this->languageService->sL(
                'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.flash.flush.header'
            ),
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK,
            true
        );
    }

    private function getNoCacheFoundMessage(): object
    {
        return GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->languageService->sL(
                'LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.flash.detailed.error'
            ),
            '',
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::NOTICE,
            true
        );
    }

    private function showFlashMessage(FlashMessage $message): void
    {
        $messageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);
    }
}
