<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module: Cache management, global module in Tools >
 *
 * @author	Kasper Sk�rh�j <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   72: class tx_cachemgm_mod
 *   85:     function init()
 *   98:     function jumpToUrl(URL)
 *  110:     function menuConfig()
 *  132:     function main()
 *  162:     function printContent()
 *  172:     function cache_stat()
 *  195:     function db_bm()
 *  315:     function file_bm()
 *
 *              SECTION: OTHER FUNCTIONS:
 *  387:     function getAllFoldersInPath($fileArr,$path,$recursivityLevels=99,$excludePattern='')
 *  408:     function getFolderInfo($subpath,$recursivity=99)
 *  444:     function addFileAccessInfo(&$info,$testAccess=10)
 *  495:     function addFileWriteFiles(&$info,$files=3,$contentlength=100000)
 *  567:     function debugRows($rows,$header='',$returnHTML=FALSE,$noHSC=FALSE)
 *
 * TOTAL FUNCTIONS: 13
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');

$BE_USER->modAccess($MCONF,1);

/**
 * Backend module providing cache management overview and performance analysis.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_cachemgm
 */
class tx_cachemgm_mod {
	var $MCONF=array();
	var $MOD_MENU=array();
	var $MOD_SETTINGS=array();
	var $doc;

	var $content;

	/**
	 * Initialization
	 *
	 * @return	void
	 */
	function init()	{
		global $BACK_PATH;
		$this->MCONF = $GLOBALS["MCONF"];

		$this->menuConfig();

		$this->doc = t3lib_div::makeInstance("noDoc");
		$this->doc->form='<form action="" method="post">';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->styleSheetFile2 = '../typo3conf/ext/cachemgm/mod/styles.css';
		
				// JavaScript
		$this->doc->JScode = '
		<script language="javascript" type="text/javascript">
			script_ended = 0;
			function jumpToUrl(URL)	{
				window.location.href = URL;
			}
		</script>
		';
	}

	/**
	 * Configuration of menu
	 *
	 * @return	void
	 */
	function menuConfig()	{

		// MENU-ITEMS:
			// If array, then it's a selector box menu
			// If empty string it's just a variable, that'll be saved.
			// Values NOT in this array will not be saved in the settings-array for the module.
		$this->MOD_MENU = array(
			"function" => array(
				"cache_stat" => "Global Cache Tables Information",
				"cachingframework_stat" => "Cachingframework Infos",
				'db_bm' => 'SELECT benchmarks',
				'file_bm' => 'File System benchmarks'
			)
		);
			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP("SET"), $this->MCONF["name"], "ses");
	}

	/**
	 * Main dispatch function
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->content="";
		$this->content.=$this->doc->startPage("Cache Management Tools, Analysis and Benchmarking");

		$menu=t3lib_BEfunc::getFuncMenu(0,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"]);

		$this->content.=$this->doc->header("Cache Management Tools, Analysis and Benchmarking");
		$this->content.=$this->doc->spacer(5);
		$this->content.=$this->doc->section('',$menu);
		
		switch($this->MOD_SETTINGS["function"])	{
			case "cache_stat":
				$this->content.= $this->cache_stat();
			break;
			case "cachingframework_stat":
				$this->content.= $this->cachingframework_stat();
			break;
			case "db_bm":
				$this->content.= $this->db_bm();
			break;
			case "file_bm":
				$this->content.= $this->file_bm();
			break;
		}
	}

	/**
	 * Printing content
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Creates stats on the cache_hash table
	 *
	 * @return	void
	 */
	function cache_stat() {
		$output.='<input type="submit" name="_test_cache_hash" value="Count records in cache_hash"/> <br/><br />(Do not do this if you plan to run DB select analysis on the table in a moment or the numbers will reflect effects of MySQL caching)';

		if (t3lib_div::_POST('_test_cache_hash')) {
			if (!defined('TYPO3_UseCachingFramework') || !TYPO3_UseCachingFramework) {
				$cache_hash_counts = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'ident,count(*)',
					$this->getCacheHashTable(),
					'1=1',
					'ident'
				);
			} else {
				$cache_hash_counts = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'tag,count(*)',
					$this->getCacheHashTagsTable(),
					'1=1',
					'tag'
				);
			}

			$output.= 'Count:'.$this->debugRows($cache_hash_counts,'',1);
		}

		$this->content.=$this->doc->section('Showing "cache_hash" numbers:',$output);
	}

	/**
	 * Gets table name containing cache-hashes.
	 *
	 * @return string
	 */
	protected function getCacheHashTable() {
		if (version_compare(TYPO3_version, '4.6', '>=')) {
			$tableName = 'cf_cache_hash';
		} else {
			$tableName = 'cache_hash';
		}
		return $tableName;
	}

	/**
	 * Gets table name containing cache-hash tags.
	 *
	 * @return string
	 */
	protected function getCacheHashTagsTable() {
		if (version_compare(TYPO3_version, '4.6', '>=')) {
			$tableName = 'cf_cache_hash_tags';
		} else {
			$tableName = 'cachingframework_cache_hash_tags';
		}
		return $tableName;
	}
	
	/**
	 * Creates stats on the cache_hash table
	 *
	 * @return	void
	 */
	function cachingframework_stat()	{
		$infoService = t3lib_div::makeInstance('tx_cachemgm_mod_cachingFrameworkInfoService');
		$subAction = t3lib_div::_GP('cachingFrameWorkSubAction');
		$output='';
		switch ($subAction) {
			case 'details':
				$cacheId = t3lib_div::_GP('cacheId');
				$output .= $infoService->printOverviewForCache($cacheId);
				$this->content.=$this->doc->section('Details for Cache: '.$cacheId,$output);
				break;
			case 'flush':
				$cacheId = t3lib_div::_GP('cacheId');
				$output .= '<p class="warning">'.$cacheId.' flushed!</p>';
				//$infoService->flushCacheByCacheId($cacheId);
				
			default:
				$output .= "<p>You can adjust the Caching configuration in your localconf.php. Using <pre>\$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']</pre>";
				$output .= '<br> You can also use the cli log tool when you use the Statistic Variable frontend: '."\$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['extbase_object']['frontend'] = 'Tx_Cachemgm_Cache_Frontend_LogableVariableFrontend';".'</p>';
				$output .= $infoService->printOverview();
				$this->content.=$this->doc->section('Available Cache Backends:',$output);				
			break;
			
		}
		

		
	}

	/**
	 * Database Benchmarking
	 *
	 * @return	void
	 */
	function db_bm()	{

		$TEST_db_access_all = t3lib_div::_POST('_test_db_access_ALL');
		$TEST_db_access_number = t3lib_div::intInRange(t3lib_div::_POST('_test_db_access_number'),1,2000,100);

		$output.='<br/><br/>';
		$output.='<h3>DB record Access<h3/>';
		$output.='<p>This will allow you to test the reading speed of records from tables in the database by reading a number of random records from the database using the first row in each index.<br/>
					The same records will be read three times. In the first pass (Pass1) you can expect a higher number than for the next two reads (Pass2 and Pass3). This is because MySQL or file system will cache.
					The test works best when the number of records in a table row is higher than the number of records read. Especially if you perform the test multiple times you will get increasingly "better performance" in the first pass because of caching.
					Ideally your test should start out with a rebooted and non-busy website to make sure no file/db caches are full. Well, you figure...<br>
					In the sample below you can see that 100 records are tested on for the PRIMARY key. It takes 42 ms meaning each record took 0.4 ms to read. When using another index for the table only 31 records was selected. It took 14ms and divided by 31 it ends at 0.5 ms per record.<br>
					<img src="db_read.png" hspace="5" vspace="5" alt="" />
					<p/>';
		$output.='<input type="text" name="_test_db_access_number" value="'.htmlspecialchars($TEST_db_access_number).'" /> number of records to read.<br/>';
		$output.='<input type="submit" name="_test_db_access_ALL" value="Test ALL tables" onclick="return confirm(\'You sure?\');"/>';

		$output.='<br/><br/><br/>';



			// All tables:
		$allTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		$info = array();

		$navTable='<table>';
		$navTable.='<tr class="tableheader">
					<td>Table name:</td>
					<td>Records:</td>
					<td>Test:</td>
				</tr>';

		foreach($allTables as $table => $tableInformation) {
			if (isset($tableInformation['Rows'])) {
				$count = $tableInformation['Rows'];
			} else {
				$count = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('*', $table);
			}

			if (t3lib_div::_POST('_test_db_access_'.$table))	{
				$output.='<input type="submit" name="_test_db_access_'.$table.'" value="Reload Test for '.$table.'" />';
			}
			if ($TEST_db_access_all || t3lib_div::_POST('_test_db_access_'.$table))	{
					// All keys, find those that are first in sequence:
				$allKeys = $GLOBALS['TYPO3_DB']->admin_get_keys($table);

				$keysToLookUp = array();
				foreach($allKeys as $indexInfo)	{
					if ((int)$indexInfo['Seq_in_index']===1)	{
						$keysToLookUp[$indexInfo['Key_name']] = $indexInfo['Column_name'];
					}
				}

					// FOr all first-in-sequence keys, lets find a number of random values:
				foreach($keysToLookUp as $keyname=>$field)	{
					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($field.', RAND() as randNum',$table,'1=1',$field,'randNum',$TEST_db_access_number,$field);
					$info[$table.'_'.$keyname]['Table'] = $table;
					$info[$table.'_'.$keyname]['Count'] = $count;
					$info[$table.'_'.$keyname]['Key'] = $keyname;
					$info[$table.'_'.$keyname]['Field'] = $field;
					$info[$table.'_'.$keyname]['Records'] = count($rows) ? $rows : '';
				}
			}

			$navTable.='<tr>
				<td>'.$table.'</td>
				<td>'.$count.'</td>
				<td><input type="submit" name="_test_db_access_'.$table.'" value="Test" /></td>
			</tr>';
		}
		$navTable.='</table>';


			// Reading records 1:
		foreach($info as $tableKey => $data)	{
			if (is_array($data['Records']))	{
				$pt_record = t3lib_div::milliseconds();
				foreach($data['Records'] as $rec)	{
					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$data['Table'],$data['Field'].'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($rec[$data['Field']],$data['Table']),'','',1);
					#echo count($rows);
				}
				$td = t3lib_div::milliseconds()-$pt_record;
				$info[$tableKey]['Pass1'] = $td;
#				$info[$tableKey]['Pass1_Read100'] = round((t3lib_div::milliseconds()-$pt_record)/count($data['Records'])*100);
			}
		}

			// Reading records 2:
		foreach($info as $tableKey => $data)	{
			if (is_array($data['Records']))	{
				$pt_record = t3lib_div::milliseconds();
				foreach($data['Records'] as $rec)	{
					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$data['Table'],$data['Field'].'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($rec[$data['Field']],$data['Table']),'','',1);
				}
				$td = t3lib_div::milliseconds()-$pt_record;
				$info[$tableKey]['Pass2'] = $td;
			}
		}

			// Reading records 3:
		foreach($info as $tableKey => $data)	{
			if (is_array($data['Records']))	{
				$pt_record = t3lib_div::milliseconds();
				foreach($data['Records'] as $rec)	{
					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$data['Table'],$data['Field'].'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($rec[$data['Field']],$data['Table']),'','',1);
				}
				$td = t3lib_div::milliseconds()-$pt_record;
				$info[$tableKey]['Pass3'] = $td;
			}

			$info[$tableKey]['SelectedRecords:'] = count($data['Records']);
			unset($info[$tableKey]['Records']);
		}

		$output.= $this->debugRows($info,'',1);
		$output.= $navTable;
		$this->content.=$this->doc->section('Testing db access:',$output);
	}

	/**
	 * File system benchmarking
	 *
	 * @return	void
	 */
	function file_bm()	{

		$TEST_file_access = t3lib_div::_POST('_test_file_access');
		$TEST_file_write = t3lib_div::_POST('_test_file_write');
		$TEST_file_access_number = t3lib_div::intInRange(t3lib_div::_POST('_test_file_access_number'),1,200,10);
		$TEST_file_levels = t3lib_div::intInRange(t3lib_div::_POST('_test_file_levels'),0,99,0);

		$output.='<br/><br/>';
		$output.='<h3>File Access<h3/>';
		$output.='<p>For each directory listed below this test will pick a number of random files and read the full contents of them while measuring the time it takes. This can give you a hint if file access is slow on your system.<br/>
					The output shows the files read, their size and three columns 0,1,2 which shows the read time for three consecutive read operations. Usually column 0 will contain a higher number than column 1 and 2 which should be the same since the first read (column 0 time) will indicate the performance without the file system cache and read 2 and 3 (Columns 1+2) will indicate the delivery when the file system has cached the file. Also, an average is calculated.<br/>
					<img src="file_access.png" hspace="5" vspace="5" alt="" /><p/>';
		$output.='<input type="text" name="_test_file_access_number" value="'.htmlspecialchars($TEST_file_access_number).'" /> number of files picked.<br/>';
		$output.='<input type="submit" name="_test_file_access" value="Test File Access Times" />';

		$output.='<br/><br/>';
		$output.='<h3>File Write<h3/>';
		$output.='<p>Will write 3 temporary files of 100kb to each directory below, measure the time it takes to write, read and delete the files. The output will display that in milliseconds in a table like the one shown here:<br/>
					<img src="file_write.png" hspace="5" vspace="5" alt="" /><p/>';
		$output.='<input type="submit" name="_test_file_write" value="Test File Write Times" />';

		$output.='<br/><br/><br/>';

		$output.='<select name="_test_file_levels">
			<option value="0"'.($TEST_file_levels==0?' selected="selected"':'').'>No sub levels</option>
			<option value="1"'.($TEST_file_levels==1?' selected="selected"':'').'>1 sub level</option>
			<option value="2"'.($TEST_file_levels==2?' selected="selected"':'').'>2 sub levels</option>
			<option value="3"'.($TEST_file_levels==3?' selected="selected"':'').'>3 sub levels</option>
			<option value="4"'.($TEST_file_levels==4?' selected="selected"':'').'>4 sub levels</option>
			<option value="99"'.($TEST_file_levels==99?' selected="selected"':'').'>Infinite</option>
		</select><input type="submit" name="_" value="Refresh" />';


		$folderInfo = $this->getFolderInfo('fileadmin/',$TEST_file_levels);
		if($TEST_file_access)	$this->addFileAccessInfo($folderInfo,$TEST_file_access_number);
		if($TEST_file_write)	$this->addFileWriteFiles($folderInfo);
		$output.= $this->debugRows($folderInfo,'PATH: fileadmin/',1,1);

		$folderInfo = $this->getFolderInfo('typo3temp/',$TEST_file_levels);
		if($TEST_file_access)	$this->addFileAccessInfo($folderInfo,$TEST_file_access_number);
		if($TEST_file_write)	$this->addFileWriteFiles($folderInfo);
		$output.= $this->debugRows($folderInfo,'PATH: typo3temp/',1,1);

		$folderInfo = $this->getFolderInfo('uploads/',$TEST_file_levels);
		if($TEST_file_access)	$this->addFileAccessInfo($folderInfo,$TEST_file_access_number);
		if($TEST_file_write)	$this->addFileWriteFiles($folderInfo);
		$output.= $this->debugRows($folderInfo,'PATH: uploads/',1,1);

		$this->content.=$this->doc->section('Testing file access:',$output);
	}






	/***************************
	 *
	 * OTHER FUNCTIONS:
	 *
	 ***************************/

	/**
	 * Recursively gather all files and folders of a path.
	 * Usage: 5
	 *
	 * @param	array		$fileArr: Empty input array (will have files added to it)
	 * @param	string		$path: The path to read recursively from (absolute) (include trailing slash!)
	 * @param	integer		$recursivityLevels: The number of levels to dig down...
	 * @param	string		$excludePattern: regex pattern of files/directories to exclude
	 * @return	array		An array with the found files/directories.
	 */
	function getAllFoldersInPath($fileArr,$path,$recursivityLevels=99,$excludePattern='')	{
		$fileArr[] = $path;

		$dirs = t3lib_div::get_dirs($path);
		if (is_array($dirs) && $recursivityLevels>0)	{
			foreach ($dirs as $subdirs)	{
				if ((string)$subdirs!='' && (!strlen($excludePattern) || !preg_match('/^'.$excludePattern.'$/',$subdirs)))	{
					$fileArr = $this->getAllFoldersInPath($fileArr,$path.$subdirs.'/',$recursivityLevels-1,$excludePattern);
				}
			}
		}
		return $fileArr;
	}

	/**
	 * Reads information about folders in a directory
	 *
	 * @param	string		Path relative to PATH_site
	 * @param	integer		Recursivity into folders
	 * @return	array		Info array.
	 */
	function getFolderInfo($subpath,$recursivity=99)	{
		$path = PATH_site.$subpath;
		$file_bm = t3lib_div::removePrefixPathFromList($this->getAllFoldersInPath(array(),$path,$recursivity),PATH_site);
		$info = array();
		$postDir = t3lib_div::_POST('_dir');
		foreach($file_bm as $relPath)	{
			$relPath.='';

			$pt = t3lib_div::milliseconds();

			$info[$relPath] = array();
			$info[$relPath]['Directory'] = $relPath;
			$numFiles = t3lib_div::getFilesInDir(PATH_site.$relPath);
			$info[$relPath]['numFiles'] = count($numFiles);

			/*
			$sizes = 0;
			foreach($numFiles as $file)	{
				$sizes+=filesize(PATH_site.$relPath.$file);
			}
			$info[$relPath]['bytes'] = t3lib_div::formatSize($sizes);
			*/
			$info[$relPath]['statInfoMS'] = t3lib_div::milliseconds()-$pt;
			$info[$relPath]['Select'] = '<input type="checkbox" value="1" name="_dir['.htmlspecialchars($relPath).']" '.($postDir[$relPath]?'checked="checked"':'').'/>';
		}

		return $info;
	}

	/**
	 * Adds information about files access times in folders to $info array
	 *
	 * @param	array		Information array from getFoldersInfo()
	 * @param	integer		Number of random files to return
	 * @return	void
	 */
	function addFileAccessInfo(&$info,$testAccess=10)	{

		$postDir = t3lib_div::_POST('_dir');
		foreach($info as $relPath => $infA)	{
			if ($testAccess > 0 && $postDir[$relPath])	{
				$numFiles = t3lib_div::getFilesInDir(PATH_site.$relPath);
				shuffle($numFiles);

				for($a=0;$a<3;$a++)	{
					$c=0;
					reset($numFiles);
					foreach($numFiles as $file)	{
						$info[$relPath]['testAccess'][$file]['file']=$file;
						$info[$relPath]['testAccess'][$file]['size']=filesize(PATH_site.$relPath.$file);

						$pt_file=t3lib_div::milliseconds();
						$fileContents = t3lib_div::getUrl(PATH_site.$relPath.$file);
						$info[$relPath]['testAccess'][$file][$a]=t3lib_div::milliseconds()-$pt_file;

						$c++;
						if ($c>=$testAccess)	break;
					}
				}

				if (is_array($info[$relPath]['testAccess']))	{
					$aStats=array();
					foreach($info[$relPath]['testAccess'] as $k => $v)	{
						$aStats[0]+=$v[0];
						$aStats[1]+=$v[1];
						$aStats[2]+=$v[2];
					}
					$rc = count($info[$relPath]['testAccess']);
					$info[$relPath]['testAccess']['AVG']['file']='Average';
					$info[$relPath]['testAccess']['AVG'][0]=round($aStats[0]/$rc);
					$info[$relPath]['testAccess']['AVG'][1]=round($aStats[1]/$rc);
					$info[$relPath]['testAccess']['AVG'][2]=round($aStats[2]/$rc);
				}
			} else {
				$info[$relPath]['testAccess'] = '-';
			}
		}
	}

	/**
	 * Adds information about files write times in folders to $info array
	 *
	 * @param	array		Information array from getFoldersInfo()
	 * @param	integer		Number of files to create
	 * @param	integer		Content length in bytes
	 * @return	void
	 */
	function addFileWriteFiles(&$info,$files=3,$contentlength=100000)	{

		$postDir = t3lib_div::_POST('_dir');
		if ($files > 0 && $contentlength>0)	{
			$contentString = str_pad('TYPO3 Extension "cachemgm" testing file writing. Should have been deleted by writing process, if not, delete it!!   ',$contentlength,'0123456789ABCDEF'.chr(10));

			$tempFileNames = array();
			for($a=0;$a<$files;$a++)	{
				$tempFileNames[] = '_TEMP_ext_cachemgm_'.$a.'_'.md5(uniqid('lalal'));
			}

			foreach($info as $relPath => $infA)	{
				if ($postDir[$relPath])	{
						// Check if they exist:
					clearstatcache();
					foreach($tempFileNames as $fileToWrite)	{
						$info[$relPath]['testWrite'][$fileToWrite]['Write File:'] = $fileToWrite;
						if (file_exists(PATH_site.$relPath.$fileToWrite))	die(PATH_site.$relPath.$fileToWrite.' EXISTED - it should not.');
					}

						// Write them:
					clearstatcache();
					foreach($tempFileNames as $number => $fileToWrite)	{
						$pt_file=t3lib_div::milliseconds();
						t3lib_div::writeFile(PATH_site.$relPath.$fileToWrite,$contentString);
						if (file_exists(PATH_site.$relPath.$fileToWrite))	{
							$info[$relPath]['testWrite'][$fileToWrite]['write']=t3lib_div::milliseconds()-$pt_file;
						} else {
							$info[$relPath]['testWrite'][$fileToWrite]['write']='ERROR: NOT WRITTEN!';
						}
					}

						// Read back:
					clearstatcache();
					foreach($tempFileNames as $number => $fileToWrite)	{
						$pt_file=t3lib_div::milliseconds();
						t3lib_div::getURL(PATH_site.$relPath.$fileToWrite);
						if (file_exists(PATH_site.$relPath.$fileToWrite))	{
							$info[$relPath]['testWrite'][$fileToWrite]['read']=t3lib_div::milliseconds()-$pt_file;
						} else {
							$info[$relPath]['testWrite'][$fileToWrite]['read']='ERROR: NOT EXISTING!';
						}
					}

						// Delete them:
					clearstatcache();
					foreach($tempFileNames as $number => $fileToWrite)	{
						$pt_file=t3lib_div::milliseconds();
						unlink(PATH_site.$relPath.$fileToWrite);
						if (!file_exists(PATH_site.$relPath.$fileToWrite))	{
							$info[$relPath]['testWrite'][$fileToWrite]['delete']=t3lib_div::milliseconds()-$pt_file;
						} else {
							$info[$relPath]['testWrite'][$fileToWrite]['delete']='ERROR: NOT DELETED!';
						}
					}
				} else {
					$info[$relPath]['testWrite'] = '-';
				}
			}
		}
	}


	/**
	 * Displays an array as rows in a table. Useful to debug output like an array of database records.
	 *
	 * @param	array		Array of arrays with similar keys
	 * @param	string		Table header
	 * @param	boolean		If TRUE, will return content instead of echo'ing out.
	 * @param	boolean		If set, values are not htmlspecialchar()'ed. Thus allows HTML in.
	 * @return	void		Outputs to browser.
	 */
	function debugRows($rows,$header='',$returnHTML=FALSE,$noHSC=FALSE)	{
		if (is_array($rows))	{
			reset($rows);
			$firstEl = current($rows);
			if (is_array($firstEl))	{
				$headerColumns = array_keys($firstEl);
				$tRows = array();

					// Header:
				$tRows[] = '<tr><td colspan="'.count($headerColumns).'" style="background-color:#bbbbbb; font-family: verdana,arial; font-weight: bold; font-size: 10px;"><strong>'.htmlspecialchars($header).'</strong></td></tr>';
				$tCells = array();
				foreach($headerColumns as $key)	{
					$tCells[] = '
							<td><font face="Verdana,Arial" size="1"><strong>'.htmlspecialchars($key).'</strong></font></td>';
				}
				$tRows[] = '
						<tr>'.implode('',$tCells).'
						</tr>';

					// Rows:
				foreach($rows as $singleRow)	{
					$tCells = array();
					foreach($headerColumns as $key)	{
						$tCells[] = '
							<td><font face="Verdana,Arial" size="1">'.(is_array($singleRow[$key]) ? $this->debugRows($singleRow[$key],'',TRUE) : ($noHSC?$singleRow[$key]:htmlspecialchars($singleRow[$key]))).'</font></td>';
					}
					$tRows[] = '
						<tr>'.implode('',$tCells).'
						</tr>';
				}

				$table = '
					<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">'.implode('',$tRows).'
					</table>';
				if ($returnHTML)	return $table; else echo $table;
			}
		}
	}
}

// Include extension?
if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cachemgm/mod/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/cachemgm/mod/index.php"]);
}












// Make instance:
$SOBE = t3lib_div::makeInstance("tx_cachemgm_mod");
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>