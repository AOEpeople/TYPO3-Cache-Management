<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2011 Daniel Pötzinger
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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A memory log used to log and retrieve cache statistics
 *
 * @package TYPO3
 * @author Daniel Pötzinger
 * @api
 * @scope prototype
 */
class Tx_Cachemgm_Cache_MemoryLogWriter implements SingletonInterface {
	
	const ACTION_SETSTART = 'SET_START';
	const ACTION_SETEND = 'SET_END';
	
	const ACTION_GETSTART = 'GET_START';
	const ACTION_MISS = 'MISS';
	const ACTION_HIT = 'HIT';
	
	const ACTION_HASSTART = 'HAS_START';
	const ACTION_HASMISS = 'HASMISS';
	const ACTION_HASHIT = 'HASHIT';
	
	const ACTION_REQOSTART = 'REQO_START';
	const ACTION_REQOEND = 'REQO_END';
	
	
	
	const ACTION_LOGINIT = 'INIT';
	
	protected $communicationMode = 'msg'; // one of msg = Unix Mesage / shmop = Unix Shared Memory Operation / shm -  Shared memory storage
	/**
	 * incremental counter
	 * @var integer
	 */
	static protected $i;
	
	/**
	 * @var integer
	 */
	protected $processId;
	
	protected $communicationRessourceHandle;
	
	/**
	 * @var string
	 */
	protected $shmMode = 'c';
	
	
	/**
	 * @var boolean
	 */
	protected $enabled = FALSE;
	
	
	/**
	 * checks and opens shared memory
	 */
	public function __construct() {		
		$this->initCommunicationRessource();
		if (empty($this->communicationRessourceHandle)) {
			return;
		}	
		$this->enabled = TRUE;
		$this->processId = getmypid();
		$this->log('-',GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),self::ACTION_LOGINIT);
	}
	
	public function isEnabled() {
		return $this->enabled;
	}
	
	/**
	 * 
	 * @param string $cacheId
	 * @param string $identifier
	 * @param string $cacheAction
	 * @param integer $timestampStart Optional - if set it is used to caluclate and log the used time
	 * @return integer timestamp
	 */
	public function log($cacheId,$identifier, $cacheAction, $timestampStart = NULL) {
		if (!$this->isEnabled()) {
			//return;
		}
		$microtime = microtime(TRUE);
		
		$cacheLogData = array('cache'=>$cacheId,'id'=>substr($identifier,0,600), 'action'=>$cacheAction, 'timestamp'=>$microtime, 'pid'=>$this->processId , 'nr' => self::$i);
		if (!empty($timestampStart)) {
			$cacheLogData['time'] = $microtime -$timestampStart;
		}
		switch ($this->communicationMode) {
			case 'msg':
				@msg_send($this->communicationRessourceHandle, 1, $cacheLogData, true, false); 
			break;
			case 'shmop':
				@shmop_write($this->communicationRessourceHandle, serialize($cacheLogData), 0);
			break;
			case 'shm':
				@shm_put_var($this->communicationRessourceHandle,1,$cacheLogData);
			break;
		}
		self::$i++;
		return $microtime;
	}
	
	/**
	 * sets $this->communicationRessourceHandle
	 * (remains null if not sucessfull)
	 */
	private function initCommunicationRessource() {
		$this->communicationRessourceHandle = NULL;
		$key = ftok(dirname(__FILE__).'/MemoryLogWriter.php', 'R');		
		switch ($this->communicationMode) {
			case 'msg':
				$this->communicationRessourceHandle = msg_get_queue($key, 0644);
			break;
			case 'shmop':
				if (!function_exists('shmop_open')) {
					return;
				}
				$this->communicationRessourceHandle = shmop_open($key, $this->shmMode, 0644, 1000);
			break;
			case 'shm':
				$this->communicationRessourceHandle = shm_attach($key, 1000, 0644);
			break;
		}
	}

	
}
