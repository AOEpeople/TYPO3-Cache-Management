<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Daniel Pötzinger
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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;

class tx_cachemgm_mod_cachingFrameworkInfoService {
	/**
	 * @var CacheManager
	 */
	private $cacheManager;
	
	public function __construct() {
		$this->cacheManager = t3lib_div::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
	}

    /**
     * @param string $cacheId
     */
    public function flushCacheByCacheId($cacheId) {
		$cache = $this->cacheManager->getCache($cacheId);
		$cache->flush();
	}

    /**
     * @param string $cacheId
     */
	public function printOverviewForCache($cacheId) {
		$cache = $this->cacheManager->getCache($cacheId);
		$backend = $cache->getBackend();
        $overviewURL = BackendUtility::getModuleUrl(
            'tools_txcachemgmM1',
            array(
                'cachingFrameWorkSubAction' => 'overview'
            )
        );
		$content = '<a class="button" href="'.$overviewURL.'">Back to Overview</a>';
		$content .= '<ul>';
		$content .= '<li>Frontend Classname:'.get_class($cache);
		$content .= '<li>Backend Classname:'.get_class($backend);
		
		$reflectionBackend = new ReflectionObject($backend);
		$properties = $reflectionBackend->getProperties();
		foreach ($properties as $key=>$value) {
				$properties[$key]->setAccessible(true);
				$value = $properties[$key]->getValue($backend);
				if (is_object($value)) {
						$value = 'Object:'.get_class($value);
				}
				$content .= '<li>*Backend property "'.$properties[$key]->getName().'":'.$value;
		}
		
		if ($backend instanceof FileBackend) {
			$content .= '<li>Cache Folder:'.$backend->getCacheDirectory();
			if (!is_writeable($backend->getCacheDirectory())) {
				$content .='<strong class="error">Not writeable</strong>';
			}
		}
		
		if ($backend instanceof Typo3DatabaseBackend) {
			$content .= '<li>Cache Table:'.$backend->getCacheTable();
			$content .= '<li>Cache Entry Count:'.$this->countRowsInTable($backend->getCacheTable());
			
			$content .= '<li>Cache Tags Table:'.$backend->getTagsTable();
			$content .= '<li>Cache Tags Entry Count:'.$this->countRowsInTable($backend->getTagsTable());
			
			
		}
		$content .= '</ul>';
		
		return $content;
	}
	
	public function printOverview() {
		$content = '<table class="cacheoverview">';
		$content .= '<tr><th>Cache name</th><th>Type</th><th>Backend</th><th>Options</th><th></th></tr>';
		foreach ($this->getAvailableCaches() as $cacheId) {
			$conf = $this->getCacheConfiguration($cacheId);
			$content .= '<tr><td>'.$cacheId.'</td>';
			$content .= '<td>'.$this->getCacheType($cacheId).'</td>';
			$content .= '<td>'.$this->getCacheBackendType($cacheId).'</td>';
			$options = '';
			if (isset($conf['options']) && !empty($conf['options'])) {
				$options = str_replace('array','',var_export($conf['options'],true));
			}

            $detailsURL = BackendUtility::getModuleUrl(
                'tools_txcachemgmM1',
                array(
                    'cachingFrameWorkSubAction' => 'details',
                    'cacheId' => $cacheId
                )
            );

            $flushURL = BackendUtility::getModuleUrl(
                'tools_txcachemgmM1',
                array(
                    'cachingFrameWorkSubAction' => 'flush',
                    'cacheId' => $cacheId
                )
            );

			$content .= '<td>'.$options.'</td>';
			$content .='<td>
							<a class="button" href="'.$detailsURL.'">Details</a>
							<a class="button warning" href="'.$flushURL.'" onclick="return confirm(\'really?\')">Flush!</a>
						</td></tr>';
		}
		$content .= '</table>';
		return $content;
	}

	
	public function getCacheConfiguration($cacheId) {
		return $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheId];	
	}
	
	/**
	 * @return array with cache keys
	 */
	public function getAvailableCaches() {
		return array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);		
	}
	
	protected function countRowsInTable($table) {
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'count(*) as count',
					$table,
					'1=1',
					''
				);
				
		return $row[0]['count'];
	}
	
	protected function getCacheType($cacheId) {
		$conf = $this->getCacheConfiguration($cacheId);
		$frontend = $conf['frontend'];
		if (empty($frontend)) {
			return 'Default (Variable)';
		}
		return $frontend;
	}
	
	protected function getCacheBackendType($cacheId) {
		$conf = $this->getCacheConfiguration($cacheId);
		$backend = $conf['backend'];
		if (empty($backend)) {
			return 'Default (DbBackend)';
		}
		return $backend;
	}
}