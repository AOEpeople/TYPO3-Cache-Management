<?php
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
/**
 * Cache Management library
 *
 * @author    Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_cachemgm_lib
 *
 *              SECTION: tslib_fe hooks:
 *   74:     function fe_headerNoCache(&$params, $ref)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



/**
 * Cache Management Library
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_cachemgm
 */
class tx_cachemgm_lib {







	/**************************
	 *
	 * tslib_fe hooks:
	 *
	 **************************/

	/**
	 * Hook for tslib_fe which disables looking up a page in cache. That is necessary if you want to make sure to re-cache (or re-index!) a page
	 *
	 * @param	array		Parameters from frontend
	 * @param	object		TSFE object (reference under PHP5)
	 * @return	void
	 */
	function fe_headerNoCache(&$params, $ref)	{

			// Requirements are that the crawler is loaded, a crawler session is running and re-caching requested as processing instruction:
		if (t3lib_extMgm::isLoaded('crawler')
				&& $params['pObj']->applicationData['tx_crawler']['running']
				&& in_array('tx_cachemgm_recache', $params['pObj']->applicationData['tx_crawler']['parameters']['procInstructions']))	{

				// Simple log message:
			$params['pObj']->applicationData['tx_crawler']['log'][] = 'RE_CACHE (cachemgm), old status: '.$params['disableAcquireCacheData'];

				// Disables a look-up for cached page data - thus resulting in re-generation of the page even if cached.
			$ref->all = '';
		}
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cachemgm/class.tx_cachemgm_lib.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cachemgm/class.tx_cachemgm_lib.php']);
}
?>