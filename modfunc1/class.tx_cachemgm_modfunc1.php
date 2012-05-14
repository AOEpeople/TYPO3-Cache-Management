<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Cache management extension
 *
 * $Id: class.tx_cms_webinfo_lang.php,v 1.3 2004/08/26 12:18:49 typo3 Exp $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   62: class tx_cachemgm_modfunc1 extends t3lib_extobjbase
 *   69:     function modMenu()
 *   87:     function main()
 *  150:     function renderModule($tree,$page_sizes=FALSE)
 *  286:     function renderID($id)
 *  323:     function getCacheInformation($pageId,$page_sizes)
 *  348:     function sortCacheInfo($cacheInfo)
 *  382:     function getCacheInformation_entry($entryId)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_t3lib.'class.t3lib_pagetree.php');
require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');


/**
 * Cache management extension
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_cachemgm
 */
class tx_cachemgm_modfunc1 extends t3lib_extobjbase {
	/**
	 * @var boolean
	 */
	protected $useCachingFramework;

	/**
	 * Frontend cache object to table cache_pages.
	 * @var t3lib_cache_frontend_AbstractFrontend
	 */
	protected $pageCache;

	/**
	 * Creates this object.
	 */
	public function __construct() {
		$this->useCachingFramework = (defined('TYPO3_UseCachingFramework') && TYPO3_UseCachingFramework);
	}


	/**
	 * Returns the menu array
	 *
	 * @return	array
	 */
	function modMenu()	{
		global $LANG;

		return array (
			'depth' => array(
				0 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_0'),
				1 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_1'),
				2 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_2'),
				3 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_3'),
			)
		);
	}

	/**
	 * MAIN function for cache information
	 *
	 * @return	string		Output HTML for the module.
	 */
	function main()	{
		global $BACK_PATH,$LANG,$SOBE;


		$showID = t3lib_div::_GP('showID');

		if ($showID)	{
			$theOutput = $this->renderID($showID);
		} else if ($this->pObj->id)	{
			$theOutput = '';

				// Depth selector:
			$h_func = t3lib_BEfunc::getFuncMenu($this->pObj->id,'SET[depth]',$this->pObj->MOD_SETTINGS['depth'],$this->pObj->MOD_MENU['depth'],'index.php');
			$theOutput.= $h_func;

				// Showing the tree:
				// Initialize starting point of page tree:
			$treeStartingPoint = intval($this->pObj->id);
			$treeStartingRecord = t3lib_BEfunc::getRecord('pages', $treeStartingPoint);
			t3lib_BEfunc::workspaceOL('pages',$treeStartingRecord);
			$depth = $this->pObj->MOD_SETTINGS['depth'];

				// Initialize tree object:
			$tree = t3lib_div::makeInstance('t3lib_pageTree');
			$tree->init('AND '.$GLOBALS['BE_USER']->getPagePermsClause(1));

				// Creating top icon; the current page
			$HTML = t3lib_iconWorks::getIconImage('pages', $treeStartingRecord, $GLOBALS['BACK_PATH'],'align="top"');
			$tree->tree[] = array(
				'row' => $treeStartingRecord,
				'HTML' => $HTML
			);

				// Create the tree from starting point:
			if ($depth>0)	{
				$tree->getTree($treeStartingPoint, $depth, '');
			}

				// Set CSS styles specific for this document:
			$this->pObj->content = str_replace('/*###POSTCSSMARKER###*/','
				TABLE.c-list TR TD { white-space: nowrap; vertical-align: top; }
			',$this->pObj->content);


			$theOutput.= '<input type="submit" value="Redraw with page sizes" name="_show_page_sizes" />
				<input type="submit" name="_flush_all" value="Flush ALL shown cache!">
				<input type="hidden" name="id" value="'.$this->pObj->id.'" />';

				// Render information table:
			$theOutput.= $this->renderModule($tree,t3lib_div::_POST('_show_page_sizes')?TRUE:FALSE,t3lib_div::_POST('_flush_all')?TRUE:FALSE);
		}

		return $theOutput;
	}

	/**
	 * Rendering the information
	 *
	 * @param	array		The Page tree data
	 * @param	boolean		If set, page sizes are rendered.
	 * @param	boolean		If set, page cache is flushed.
	 * @return	string		HTML for the information table.
	 */
	function renderModule($tree,$page_sizes=FALSE,$flushAll=FALSE)	{

			// Traverse tree:
		$output = '';
		$cc=0;
		foreach($tree->tree as $row)	{

			if ($flushAll)	{
				/* @var $tce t3lib_TCEmain */
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->start($row['row'], 'delete');
				$tce->clear_cacheCmd(intval($row['row']['uid']));
			}

				// Fetch cache information:
			$cacheInfo = $this->getCacheInformation($row['row']['uid'],$page_sizes);

				// Row title:
			$rowTitle = $row['HTML'].t3lib_BEfunc::getRecordTitle('pages',$row['row'],TRUE);
			$cellAttrib = ($row['row']['_CSSCLASS'] ? ' class="'.$row['row']['_CSSCLASS'].'"' : '');

				// Add at least one empty element:
			if (!count($cacheInfo))	{
						// Add title:
					$tCells = array();
					$tCells[]='<td nowrap="nowrap"'.$cellAttrib.'>'.$rowTitle.'</td>';

						// Empty row:
					$tCells[]='<td colspan="'.($page_sizes?15:13).'" align="center">&nbsp;</td>';

						// Compile Row:
					$output.= '
						<tr class="bgColor'.($cc%2 ? '-20':'-10').'">
							'.implode('
							',$tCells).'
						</tr>';
					$cc++;
			} elseif (count($cacheInfo)>50 && ($row['row']['uid']!=$this->pObj->id || $this->pObj->MOD_SETTINGS['depth']>0))	{
						// Add title:
					$tCells = array();
					$tCells[]='<td nowrap="nowrap"'.$cellAttrib.'>'.$rowTitle.'</td>';

						// Empty row:
					$tCells[]='<td colspan="'.($page_sizes?15:13).'" align="center"><em>'.count($cacheInfo).' entries found, <a href="index.php?id='.$row['row']['uid'].'&SET[depth]=0">click here to view them.</a></em></td>';

						// Compile Row:
					$output.= '
						<tr class="bgColor'.($cc%2 ? '-20':'-10').'">
							'.implode('
							',$tCells).'
						</tr>';
					$cc++;
			} else {
				$cacheInfo = $this->sortCacheInfo($cacheInfo);

				foreach($cacheInfo as $c => $inf)	{

						// Cache meta data:
					$cacheMetaData = unserialize($inf['cache_data']);
					$hash_base = unserialize($cacheMetaData['hash_base']);
					if (is_array($hash_base))	unset($hash_base['cHash']['encryptionKey']);

						// Add title:
					$tCells = array();
					if (!$c)	{
						$tCells[]='<td nowrap="nowrap" rowspan="'.count($cacheInfo).'"'.$cellAttrib.'>'.$rowTitle.'</td>';
						$tCells[]='<td rowspan="'.count($cacheInfo).'">'.$inf['page_id'].'</td>';
						$tCells[]='<td rowspan="'.count($cacheInfo).'">'.count($cacheInfo).'</td>';
					}
					$tCells[]='<td align="center">'.htmlspecialchars($hash_base['type']).'</td>';
					$tCells[]='<td>'.htmlspecialchars($hash_base['MP']).'</td>';
					$tCells[]='<td align="center">'.(is_array($cacheMetaData) ? htmlspecialchars($cacheMetaData['config']['sys_language_uid']) : '').'</td>';
					$tCells[]='<td>'.htmlspecialchars($hash_base['gr_list']).'</td>';
					$tCells[]='<td>'.(is_array($hash_base['cHash']) && count($hash_base['cHash']) ? htmlspecialchars(t3lib_div::fixed_lgd_cs(t3lib_div::implodeArrayForUrl('',$hash_base['cHash']),200)) : '').'</td>';
					$tCells[]='<td>'.t3lib_div::shortMd5(serialize($hash_base['all'])).'</td>';

					if ($page_sizes)	{
						$tCells[]='<td nowrap="nowrap">'.t3lib_div::formatSize(strlen($inf['HTML'])).'</td>';
						$tCells[]='<td nowrap="nowrap">'.t3lib_div::shortMd5($inf['HTML']).'</td>';
					}
					$tCells[]='<td>'.htmlspecialchars($inf['reg1']?$inf['reg1']:'').'</td>';
					$tCells[]='<td nowrap="nowrap">'.htmlspecialchars(t3lib_BEfunc::datetime($inf['tstamp'])).' / '.htmlspecialchars(t3lib_BEfunc::calcAge($inf['tstamp']-time())).'</td>';
					$tCells[]='<td nowrap="nowrap">'.htmlspecialchars(t3lib_BEfunc::datetime($inf['expires'])).' / '.htmlspecialchars(t3lib_BEfunc::calcAge($inf['expires']-time())).'</td>';

					$id = $this->useCachingFramework ? '' : $inf['id'];
					$hash = $this->useCachingFramework ? $inf['identifier'] : $inf['hash'];

					$tCells[]='<td>'.($id?(htmlspecialchars($id).' - '):'').'<a href="index.php?id='.$this->pObj->id.'&showID='.htmlspecialchars($hash).'"><u>Details</u></a></td>';
					$tCells[]='<td>'.htmlspecialchars($hash).'</td>';

						// Compile Row:
					$trClass = ($page_sizes && strlen($inf['HTML']) && strlen($inf['HTML'])<1000 ? 'bgColor6' : 'bgColor'.($cc%2 ? '-20':'-10'));
					$output.= '
						<tr class="'.$trClass.'">
							'.implode('
							',$tCells).'
						</tr>';
					$cc++;
				}
			}
		}

			// Create header:
		$tCells = array();
		$tCells[]='<td>Page:</td>';
		$tCells[]='<td>ID:</td>';
		$tCells[]='<td>Cnt:</td>';

		$tCells[]='<td>&type:</td>';
		$tCells[]='<td>&MP:</td>';
		$tCells[]='<td>Lang:</td>';
		$tCells[]='<td>Groups:</td>';
		$tCells[]='<td>&cHash:</td>';
		$tCells[]='<td>Tmpl-Hash:</td>';

		if ($page_sizes)	{
			$tCells[]='<td>Size:</td>';
			$tCells[]='<td>Content:</td>';
		}
		$tCells[]='<td>Reg1:</td>';
		$tCells[]='<td>Created/Updated:</td>';
		$tCells[]='<td>Expires:</td>';

		$tCells[]='<td>UID:</td>';
		$tCells[]='<td>Entry Hash String:</td>';

		$output = '
			<tr class="bgColor5 tableheader">
				'.implode('
				',$tCells).'
			</tr>'.$output;

			// Compile final table and return:
		$output = '
		<table border="0" cellspacing="1" cellpadding="0" class="lrPadding c-list">'.$output.'
		</table>';

		return $output;
	}

	/**
	 * Shows details about a single cache entry.
	 *
	 * @param	string		cache entry identifier.
	 * @return	string		HTML
	 */
	function renderID($identifier) {
		$cache_row = $this->getCacheInformation_entry($identifier);

		$html = $cache_row['HTML'];
		unset($cache_row['HTML']);

		$cache_data = unserialize($cache_row['cache_data']);
		$cache_data['hash_base'] = unserialize($cache_data['hash_base']);
		unset($cache_row['cache_data']);

		ob_start();
		print_r($cache_data);
		$cache_data_formatted = ob_get_contents();
		ob_end_clean();


		$theOutput.= '<h3>Cache record</h3><em>(except HTML and cache_data fields)</em>'.
						t3lib_div::view_array($cache_row);

		$theOutput.= '<h3>"HTML" field content:</h3>';
		$theOutput.= '<pre>'.htmlspecialchars($html).'</pre>';

		$theOutput.= '<h3>"cache_data" field content:</h3>';
		$theOutput.= '<pre>'.htmlspecialchars($cache_data_formatted).'</pre>';

		$theOutput.= '<a href="index.php?id='.$this->pObj->id.'">Back</a>';

		return $theOutput;
	}

	/**
	 * Fetch caching information for page.
	 *
	 * @param	integer		Page ID
	 * @param	boolean		Selects content column as well if set.
	 * @return	array		Page Cache records
	 */
	function getCacheInformation($pageId, $page_sizes) {
		$cachedPages = array();
		$pageId = intval($pageId);

		if (!$this->useCachingFramework) {
			$fieldList = 'id,hash,page_id,reg1,tstamp,expires,cache_data,temp_content';

			if ($page_sizes) {
				$fieldList .= ',HTML';
			}

			$cachedPages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				$fieldList,
				'cache_pages',
				'page_id=' . $pageId,
				'',
				'reg1'
			);
		} else {
			$cachedPages = $this->getPageCache()->getByTag('pageId_' . $pageId);
		}

		return $cachedPages;
	}

	/**
	 * Sorting cache info rows:
	 *
	 * @param	array		Ordering the cache info table so it becomes easier to get an overview of.
	 * @return	array		Re-ordered array
	 */
	function sortCacheInfo($cacheInfo)	{
		$sortCacheInfo = array();
		foreach($cacheInfo as $c => $inf)	{

				// Cache meta data:
			$cacheMetaData = unserialize($inf['cache_data']);
			$hash_base = unserialize($cacheMetaData['hash_base']);
			if (is_array($hash_base))	unset($hash_base['cHash']['encryptionKey']);

			$sortCacheInfo[
				str_pad($hash_base['type'],3,'0',STR_PAD_LEFT).'_'.
				str_pad($hash_base['MP'],10,' ',STR_PAD_LEFT).'_'.
				str_pad($hash_base['gr_list'],10,' ',STR_PAD_LEFT).'_'.
				str_pad((is_array($cacheMetaData) ? htmlspecialchars($cacheMetaData['config']['sys_language_uid']) : ''),3,'0',STR_PAD_LEFT).'_'.
				(is_array($hash_base['cHash']) && count($hash_base['cHash']) ? htmlspecialchars(t3lib_div::fixed_lgd_cs(t3lib_div::implodeArrayForUrl('',$hash_base['cHash']),200)) : '').'_'.
				t3lib_div::shortMd5(serialize($hash_base['all']))
				] = $c;
		}

		ksort($sortCacheInfo);

		$newCacheInfo = array();
		foreach($sortCacheInfo as $c)	{
			$newCacheInfo[] = $cacheInfo[$c];
		}

		return $newCacheInfo;
	}

	/**
	 * Fetch caching information for page.
	 *
	 * @param	integer		Page Cache identifier
	 * @return	array		Page Cache record
	 */
	function getCacheInformation_entry($identifier) {
		$cacheInformation = array();

		if (!$this->useCachingFramework) {
			$identifier = intval($identifier);
			$cachedPages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'cache_pages', 'hash=' . $identifier);
			if (is_array($cachedPages) && count($cachedPages)) {
				$cacheInformation = $cachedPages[0];
			}
		} else {
			$cachedPage = $this->getPageCache()->get($identifier);
			if ($cachedPage !== FALSE) {
				$cacheInformation = $cachedPage;
			}
		}

		return $cacheInformation;
	}

	/**
	 * Gets the pages cache object (if caching framework is enabled).
	 *
	 * @return t3lib_cache_frontend_AbstractFrontend
	 */
	protected function getPageCache() {
		if (!$this->useCachingFramework) {
			throw new RuntimeException('Caching framework is not enabled.');
		}

		if (!isset($this->pageCache)) {
			$this->pageCache = $GLOBALS['typo3CacheManager']->getCache('cache_pages');
		}

		return $this->pageCache;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cachemgm/modfunc1/class.tx_cachemgm_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cachemgm/modfunc1/class.tx_cachemgm_modfunc1.php']);
}
?>
