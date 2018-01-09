<?php
namespace Aoe\Cachemgm\Hook;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Cache Management Library
 *
 * @author    Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_cachemgm
 */
class TypoScriptFrontendHook
{
    /**
     * Hook for TypoScriptFrontendController which disables looking up a page in cache.
     * That is necessary if you want to make sure to re-cache (or re-index!) a page
     *
     * @param    array $params Parameters from frontend
     * @param    TypoScriptFrontendController $ref
     * @return    void
     * @see TypoScriptFrontendController::headerNoCache()
     */
    public function fe_headerNoCache(&$params, TypoScriptFrontendController $ref)
    {
        if ($this->isCrawlerRunningAndRecachingPage($ref)) {
            // Simple log message:
            $ref->applicationData['tx_crawler']['log'][] = 'RE_CACHE (cachemgm), old status: ' . $params['disableAcquireCacheData'];

            // Disables a look-up for cached page data - thus resulting in re-generation of the page even if cached.
            $params['disableAcquireCacheData'] = true;
        }
    }

    /**
     * Check if crawler is loaded, a crawler session is running and re-caching is requested as processing instruction
     *
     * @param TypoScriptFrontendController $tsfe
     * @return boolean
     */
    private function isCrawlerRunningAndRecachingPage(TypoScriptFrontendController $tsfe)
    {
        if (ExtensionManagementUtility::isLoaded('crawler')
            && $tsfe->applicationData['tx_crawler']['running']
            && in_array('tx_cachemgm_recache',
                $tsfe->applicationData['tx_crawler']['parameters']['procInstructions'])) {
            return true;
        }
        return false;
    }
}
