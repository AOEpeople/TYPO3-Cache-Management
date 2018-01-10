<?php

namespace Aoe\Cachemgm\Backend;

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

use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Cache management extension
 *
 * @author    Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_cachemgm
 */
class BackendModule extends AbstractFunctionModule
{
    /**
     * Frontend cache object to table cache_pages.
     * @var AbstractFrontend
     */
    protected $pageCache;

    /**
     * Returns the menu array
     *
     * @return array
     */
    public function modMenu()
    {
        global $LANG;

        return [
            'depth' => [
                0 => $LANG->sL('LLL:EXT:lang/locallang_core.xlf:labels.depth_0'),
                1 => $LANG->sL('LLL:EXT:lang/locallang_core.xlf:labels.depth_1'),
                2 => $LANG->sL('LLL:EXT:lang/locallang_core.xlf:labels.depth_2'),
                3 => $LANG->sL('LLL:EXT:lang/locallang_core.xlf:labels.depth_3')
            ]
        ];
    }

    /**
     * MAIN function for cache information
     *
     * @return    string        Output HTML for the module.
     */
    public function main()
    {
        $showID = GeneralUtility::_GP('showID');

        if ($showID) {
            $theOutput = $this->renderID($showID);
        } else {
            if ($this->pObj->id) {
                $theOutput = '';

                // Depth selector:
                $h_func = BackendUtility::getFuncMenu($this->pObj->id, 'SET[depth]', $this->pObj->MOD_SETTINGS['depth'],
                    $this->pObj->MOD_MENU['depth'], 'index.php');
                $theOutput .= $h_func;

                // Showing the tree:
                // Initialize starting point of page tree:
                $treeStartingPoint = intval($this->pObj->id);
                $treeStartingRecord = BackendUtility::getRecord('pages', $treeStartingPoint);
                BackendUtility::workspaceOL('pages', $treeStartingRecord);
                $depth = $this->pObj->MOD_SETTINGS['depth'];

                // Initialize tree object:
                $tree = GeneralUtility::makeInstance(PageTreeView::class);
                $tree->init('AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1));

                // Creating top icon; the current page
                /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $HTML = $iconFactory->getIconForRecord('pages', $this->pObj->pageinfo,
                    \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render();

                $tree->tree[] = array(
                    'row' => $treeStartingRecord,
                    'HTML' => $HTML
                );

                // Create the tree from starting point:
                if ($depth > 0) {
                    $tree->getTree($treeStartingPoint, $depth, '');
                }

                // Set CSS styles specific for this document:
                $this->pObj->content = str_replace('/*###POSTCSSMARKER###*/', '
				TABLE.c-list TR TD { white-space: nowrap; vertical-align: top; }
			', $this->pObj->content);


                $theOutput .= '<input type="submit" value="Redraw with page sizes" name="_show_page_sizes" />
				<input type="submit" name="_flush_all" value="Flush ALL shown cache!">
				<input type="hidden" name="id" value="' . $this->pObj->id . '" />';

                // Render information table:
                $theOutput .= $this->renderModule($tree, GeneralUtility::_POST('_show_page_sizes') ? true : false,
                    GeneralUtility::_POST('_flush_all') ? true : false);
            }
        }

        return $theOutput;
    }

    /**
     * Rendering the information
     *
     * @param    PageTreeView $tree The Page tree data
     * @param    boolean $page_sizes If set, page sizes are rendered.
     * @param    boolean $flushAll If set, page cache is flushed.
     * @return    string        HTML for the information table.
     */
    private function renderModule(PageTreeView $tree, $page_sizes = false, $flushAll = false)
    {
        // Traverse tree:
        $output = '';
        $cc = 0;
        foreach ($tree->tree as $row) {
            if ($flushAll) {
                /* @var $dataHandler DataHandler */
                $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $dataHandler->start($row['row'], 'delete');
                $dataHandler->clear_cacheCmd(intval($row['row']['uid']));
            }

            // Fetch cache information:
            $cacheInfo = $this->getCacheInformation($row['row']['uid'], $page_sizes);

            // Row title:
            $rowTitle = $row['HTML'] . BackendUtility::getRecordTitle('pages', $row['row'], true);
            $cellAttrib = ($row['row']['_CSSCLASS'] ? ' class="' . $row['row']['_CSSCLASS'] . '"' : '');

            // Add at least one empty element:
            if (!count($cacheInfo)) {
                // Add title:
                $tCells = array();
                $tCells[] = '<td nowrap="nowrap"' . $cellAttrib . '>' . $rowTitle . '</td>';

                // Empty row:
                $tCells[] = '<td colspan="' . ($page_sizes ? 15 : 13) . '" align="center">&nbsp;</td>';

                // Compile Row:
                $output .= '
						<tr class="bgColor' . ($cc % 2 ? '-20' : '-10') . '">
							' . implode('
							', $tCells) . '
						</tr>';
                $cc++;
            } elseif (count($cacheInfo) > 50 && ($row['row']['uid'] != $this->pObj->id || $this->pObj->MOD_SETTINGS['depth'] > 0)) {
                // Add title:
                $tCells = array();
                $tCells[] = '<td nowrap="nowrap"' . $cellAttrib . '>' . $rowTitle . '</td>';

                // Empty row:
                $tCells[] = '<td colspan="' . ($page_sizes ? 15 : 13) . '" align="center"><em>' . count($cacheInfo) . ' entries found, <a href="index.php?id=' . $row['row']['uid'] . '&SET[depth]=0">click here to view them.</a></em></td>';

                // Compile Row:
                $output .= '
						<tr class="bgColor' . ($cc % 2 ? '-20' : '-10') . '">
							' . implode('
							', $tCells) . '
						</tr>';
                $cc++;
            } else {
                $cacheInfo = $this->sortCacheInfo($cacheInfo);

                foreach ($cacheInfo as $c => $inf) {

                    // Cache meta data:
                    $cacheMetaData = $inf['cache_data'];
                    $hash_base = unserialize($cacheMetaData['hash_base']);
                    if (is_array($hash_base)) {
                        unset($hash_base['cHash']['encryptionKey']);
                    }

                    // Add title:
                    $tCells = array();
                    if (!$c) {
                        $tCells[] = '<td nowrap="nowrap" rowspan="' . count($cacheInfo) . '"' . $cellAttrib . '>' . $rowTitle . '</td>';
                        $tCells[] = '<td rowspan="' . count($cacheInfo) . '">' . $inf['page_id'] . '</td>';
                        $tCells[] = '<td rowspan="' . count($cacheInfo) . '">' . count($cacheInfo) . '</td>';
                    }
                    $tCells[] = '<td align="center">' . htmlspecialchars($hash_base['type']) . '</td>';
                    $tCells[] = '<td>' . htmlspecialchars($hash_base['MP']) . '</td>';
                    $tCells[] = '<td align="center">' . (is_array($cacheMetaData) ? htmlspecialchars($cacheMetaData['config']['sys_language_uid']) : '') . '</td>';
                    $tCells[] = '<td>' . htmlspecialchars($hash_base['gr_list']) . '</td>';
                    $tCells[] = '<td>' . (is_array($hash_base['cHash']) && count($hash_base['cHash']) ? htmlspecialchars(GeneralUtility::fixed_lgd_cs(GeneralUtility::implodeArrayForUrl('',
                            $hash_base['cHash']), 200)) : '') . '</td>';
                    $tCells[] = '<td>' . GeneralUtility::shortMd5(serialize($hash_base['all'])) . '</td>';

                    if ($page_sizes) {
                        $tCells[] = '<td nowrap="nowrap">' . GeneralUtility::formatSize(strlen($inf['HTML'])) . '</td>';
                        $tCells[] = '<td nowrap="nowrap">' . GeneralUtility::shortMd5($inf['HTML']) . '</td>';
                    }
                    $tCells[] = '<td>' . htmlspecialchars($inf['reg1'] ? $inf['reg1'] : '') . '</td>';
                    $tCells[] = '<td nowrap="nowrap">' . htmlspecialchars(BackendUtility::datetime($inf['tstamp'])) . ' / ' . htmlspecialchars(BackendUtility::calcAge($inf['tstamp'] - time())) . '</td>';
                    $tCells[] = '<td nowrap="nowrap">' . htmlspecialchars(BackendUtility::datetime($inf['expires'])) . ' / ' . htmlspecialchars(BackendUtility::calcAge($inf['expires'] - time())) . '</td>';

                    $id = '';
                    $hash = $inf['identifier'];

                    $cacheDetailUrl = BackendUtility::getModuleUrl(GeneralUtility::_GET('M'),
                        array('id' => $this->pObj->id, 'showID' => htmlspecialchars($hash)));
                    $tCells[] = '<td>' . ($id ? (htmlspecialchars($id) . ' - ') : '') . '<a href="' . $cacheDetailUrl . '"><u>' . $GLOBALS['LANG']->sL('LLL:EXT:cachemgm/locallang.xlf:details',
                            true) . '</u></a></td>';
                    $tCells[] = '<td>' . htmlspecialchars($hash) . '</td>';

                    // Compile Row:
                    $trClass = ($page_sizes && strlen($inf['HTML']) && strlen($inf['HTML']) < 1000 ? 'bgColor6' : 'bgColor' . ($cc % 2 ? '-20' : '-10'));
                    $output .= '
						<tr class="' . $trClass . '">
							' . implode('
							', $tCells) . '
						</tr>';
                    $cc++;
                }
            }
        }

        // Create header:
        $tCells = array();
        $tCells[] = '<td>Page:</td>';
        $tCells[] = '<td>ID:</td>';
        $tCells[] = '<td>Cnt:</td>';

        $tCells[] = '<td>&type:</td>';
        $tCells[] = '<td>&MP:</td>';
        $tCells[] = '<td>Lang:</td>';
        $tCells[] = '<td>Groups:</td>';
        $tCells[] = '<td>&cHash:</td>';
        $tCells[] = '<td>Tmpl-Hash:</td>';

        if ($page_sizes) {
            $tCells[] = '<td>Size:</td>';
            $tCells[] = '<td>Content:</td>';
        }
        $tCells[] = '<td>Reg1:</td>';
        $tCells[] = '<td>Created/Updated:</td>';
        $tCells[] = '<td>Expires:</td>';

        $tCells[] = '<td>UID:</td>';
        $tCells[] = '<td>Entry Hash String:</td>';

        $output = '
			<tr class="bgColor5 tableheader">
				' . implode('
				', $tCells) . '
			</tr>' . $output;

        // Compile final table and return:
        $output = '
		<table border="0" cellspacing="1" cellpadding="0" class="lrPadding c-list">' . $output . '
		</table>';

        return $output;
    }

    /**
     * Shows details about a single cache entry.
     *
     * @param    string        cache entry identifier.
     * @return    string        HTML
     */
    private function renderID($identifier)
    {
        $cache_row = $this->getCacheInformation_entry($identifier);

        $html = $cache_row['HTML'];
        unset($cache_row['HTML']);

        $cache_data = $cache_row['cache_data'];
        unset($cache_row['cache_data']);

        ob_start();
        print_r($cache_data);
        $cache_data_formatted = ob_get_contents();
        ob_end_clean();


        $theOutput = '<h3>Cache record</h3><em>(except HTML and cache_data fields)</em>' . DebugUtility::viewArray($cache_row);

        $theOutput .= '<h3>"HTML" field content:</h3>';
        $theOutput .= '<pre>' . htmlspecialchars($html) . '</pre>';

        $theOutput .= '<h3>"cache_data" field content:</h3>';
        $theOutput .= '<pre>' . htmlspecialchars($cache_data_formatted) . '</pre>';

        $cacheListViewUrl = BackendUtility::getModuleUrl(GeneralUtility::_GET('M'), array('id' => $this->pObj->id));
        $theOutput .= '<a href="' . $cacheListViewUrl . '">' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xlf:back',
                true) . '</a>';

        return $theOutput;
    }

    /**
     * Fetch caching information for page.
     *
     * @param    integer $pageId Page ID
     * @return    array        Page Cache records
     */
    private function getCacheInformation($pageId)
    {
        $cachedPages = $this->getPageCache()->getByTag('pageId_' . $pageId);
        return $cachedPages;
    }

    /**
     * Sorting cache info rows:
     *
     * @param    array $cacheInfo Ordering the cache info table so it becomes easier to get an overview of.
     * @return    array        Re-ordered array
     */
    private function sortCacheInfo($cacheInfo)
    {
        $sortCacheInfo = array();
        foreach ($cacheInfo as $c => $inf) {

            // Cache meta data:
            $cacheMetaData = $inf['cache_data'];
            $hash_base = unserialize($cacheMetaData['hash_base']);
            if (is_array($hash_base)) {
                unset($hash_base['cHash']['encryptionKey']);
            }

            $sortCacheInfo[str_pad($hash_base['type'], 3, '0', STR_PAD_LEFT) . '_' .
            str_pad($hash_base['MP'], 10, ' ', STR_PAD_LEFT) . '_' .
            str_pad($hash_base['gr_list'], 10, ' ', STR_PAD_LEFT) . '_' .
            str_pad((is_array($cacheMetaData) ? htmlspecialchars($cacheMetaData['config']['sys_language_uid']) : ''), 3,
                '0', STR_PAD_LEFT) . '_' .
            (is_array($hash_base['cHash']) && count($hash_base['cHash']) ? htmlspecialchars(GeneralUtility::fixed_lgd_cs(GeneralUtility::implodeArrayForUrl('',
                $hash_base['cHash']), 200)) : '') . '_' .
            GeneralUtility::shortMd5(serialize($hash_base['all']))] = $c;
        }

        ksort($sortCacheInfo);

        $newCacheInfo = array();
        foreach ($sortCacheInfo as $c) {
            $newCacheInfo[] = $cacheInfo[$c];
        }

        return $newCacheInfo;
    }

    /**
     * Fetch caching information for page.
     *
     * @param    integer $identifier Page Cache identifier
     * @return    array        Page Cache record
     */
    private function getCacheInformation_entry($identifier)
    {
        $cachedPage = $this->getPageCache()->get($identifier);
        if ($cachedPage !== false) {
            return $cachedPage;
        }
        return array();
    }

    /**
     * Gets the pages cache object (if caching framework is enabled).
     *
     * @return AbstractFrontend
     */
    protected function getPageCache()
    {
        if (!isset($this->pageCache)) {
            $typo3CacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $this->pageCache = $typo3CacheManager->getCache('cache_pages');
        }
        return $this->pageCache;
    }
}
