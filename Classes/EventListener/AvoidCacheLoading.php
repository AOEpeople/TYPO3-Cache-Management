<?php

declare(strict_types=1);

namespace Aoe\Cachemgm\EventListener;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ShouldUseCachedPageDataIfAvailableEvent;

class AvoidCacheLoading
{
    public function __invoke(ShouldUseCachedPageDataIfAvailableEvent $event): void
    {
        $TypoScriptFrontendController = $event->getController();

        if ($this->isCrawlerRunningAndRecachingPage($TypoScriptFrontendController)) {
            // Simple log message:
            $TypoScriptFrontendController->applicationData['tx_crawler']['log'][] = 'RE_CACHE (cachemgm), old status: ' . $event->shouldUseCachedPageData();

            // Disables a look-up for cached page data - thus resulting in re-generation of the page even if cached.
            $event->setShouldUseCachedPageData(false);
        }
    }

    /**
     * Check if crawler is loaded, a crawler session is running and re-caching is requested as processing instruction
     */
    private function isCrawlerRunningAndRecachingPage(TypoScriptFrontendController $tsfe): bool
    {
        if (!ExtensionManagementUtility::isLoaded('crawler')) {
            return false;
        }

        if (!isset($tsfe->applicationData['tx_crawler'])) {
            return false;
        }

        return $tsfe->applicationData['tx_crawler']['running']
            && in_array(
                'tx_cachemgm_recache',
                $tsfe->applicationData['tx_crawler']['parameters']['procInstructions'],
                true
            );
    }
}
