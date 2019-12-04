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
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Lang\LanguageService;

class BackendModuleController extends ActionController
{

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var LanguageService
     */
    protected $languageService;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->cacheManager = $this->objectManager->get(CacheManager::class);
        $this->languageService = $GLOBALS['LANG'];
    }

    /**
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        $this->view->setLayoutRootPaths(['EXT:cachemgm/Resources/Private/Layouts']);
        $this->view->setPartialRootPaths(['EXT:cachemgm/Resources/Private/Partials']);
        $this->view->setTemplateRootPaths(['EXT:cachemgm/Resources/Private/Templates/BackendModule']);
    }

    public function indexAction()
    {
        $this->view->assignMultiple([
            'cacheConfigurations' => $this->buildCacheConfigurationArray(),
            'action_confirm_flush_message' => $this->languageService->sL('LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.action_confirm_flush')
            ]);
    }

    public function detailAction()
    {
        try {
            $cacheId = $this->request->getArgument('cacheId');
        } catch (NoSuchArgumentException $e) {
            $this->showFlashMessage($this->getNoCacheFoundMessage());
            $this->forward('index');
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
                    'Frontend Classname' => get_class($cache),
                    'Backend Classname' => get_class($backend),
                ],
                'fileBackend' => $fileBackend,
                'cacheCount' => $cacheCount,
                'properties' => $propertiesArray,
                'overviewLink' => $this->getHref('BackendModule', 'index'),
            ]
        );
    }

    public function flushAction()
    {
        try {
            $cacheId = $this->request->getArgument('cacheId');
        } catch (NoSuchArgumentException $e) {
            $this->showFlashMessage($this->getNoCacheFoundMessage());
            $this->forward('index');
        }
        $cache = $this->cacheManager->getCache($cacheId);
        $cache->flush();
        $this->showFlashMessage($this->getFlushCacheMessage($cacheId));
        $this->forward('index');
    }

    private function buildCacheConfigurationArray()
    {
        $cacheConfigurations = [];

        foreach (CacheUtility::getAvailableCaches() as $cacheId) {
            $cacheConfigurations[] =
                [
                    'name' => $cacheId,
                    'type' => CacheUtility::getCacheType($cacheId),
                    'backend' => CacheUtility::getCacheBackendType($cacheId),
                    'options' => CacheUtility::getCacheOptions($cacheId),
                    'detailsUrl' => $this->getHref('BackendModule', 'detail', ['cacheId' => $cacheId]),
                    'flushUrl' => $this->getHref('BackendModule', 'flush', ['cacheId' => $cacheId]),
                ];
        }

        return $cacheConfigurations;
    }

    /**
     * Creates te URI for a backend action
     *
     * @param string $controller
     * @param string $action
     * @param array $parameters
     * @return string
     */
    private function getHref($controller, $action, $parameters = [])
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->reset()->uriFor($action, $parameters, $controller);
    }

    /**
     * @param BackendInterface $backend
     * @return array
     */
    private function getBackendCacheProperties(BackendInterface $backend): array
    {
        $reflectionBackend = new \ReflectionObject($backend);
        $properties = $reflectionBackend->getProperties();
        $propertiesArray = [];
        foreach ($properties as $key => $value) {
            $properties[$key]->setAccessible(true);
            $value = $properties[$key]->getValue($backend);
            if (is_object($value)) {
                $value = 'Object: ' . get_class($value);
            }
            $propertiesArray[$properties[$key]->getName()] = $value;
        }
        return $propertiesArray;
    }

    /**
     * @param BackendInterface $backend
     * @return array|string
     */
    private function getFileBackendInfo(BackendInterface $backend)
    {
        $fileBackend = [];
        if ($backend instanceof FileBackend) {
            $fileBackend = 'Cache Folder: ' . $backend->getCacheDirectory();
            if (!is_writable($backend->getCacheDirectory())) {
                $fileBackend .= '&nbsp;<span class="badge badge-danger">' .
                    $this->languageService->sL('LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.warning.not_writeable')
                    . '</span>';
            }
        }
        return $fileBackend;
    }

    /**
     * @param $backend
     * @return array
     */
    private function getCacheCount($backend): array
    {
        $cacheTableRepository = $this->objectManager->get(CacheTableRepository::class);

        $cacheCount = [];
        if ($backend instanceof Typo3DatabaseBackend) {
            $cacheCount['Cache Table'] = $backend->getCacheTable();
            $cacheCount['Cache Entry Count'] = $cacheTableRepository->countRowsInTable($backend->getCacheTable());
            $cacheCount['Cache Tags Table'] = $backend->getTagsTable();
            $cacheCount['Cache Tags Entry Count'] = $cacheTableRepository->countRowsInTable($backend->getTagsTable());
        }
        return $cacheCount;
    }

    /**
     * @param $cacheId
     * @return object|FlashMessage
     */
    private function getFlushCacheMessage($cacheId)
    {
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            sprintf(
                $this->languageService->sL('LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.flash.flush.success'),
                $cacheId
            ),
            $this->languageService->sL('LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.flash.flush.header'),
            FlashMessage::OK,
            true
        );
        return $message;
    }

    /**
     * @return object|FlashMessage
     */
    private function getNoCacheFoundMessage()
    {
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->languageService->sL('LLL:EXT:cachemgm/Resources/Private/BackendModule/Language/locallang.xlf:bemodule.flash.detailed.error'),
            '',
            FlashMessage::NOTICE,
            true
        );
        return $message;
    }

    /**
     * @param FlashMessage $message
     */
    private function showFlashMessage(FlashMessage $message)
    {
        $messageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);
    }
}
