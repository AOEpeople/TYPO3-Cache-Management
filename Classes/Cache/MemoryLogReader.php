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

/**
 * A memory log used to log and retrieve cache statistics
 *
 * @package TYPO3
 * @author Daniel Pötzinger
 * @api
 * @scope prototype
 */
class Tx_Cachemgm_Cache_MemoryLogReader extends Tx_Cachemgm_Cache_MemoryLogWriter {
	
	/**
	 * @var string
	 */
	protected $shmMode = 'a';
	
	/**
	 * Blocking wrapper call to get log - will return the next log matching the criteria.
	 * @param integer $previousNr
	 * @param string $cacheFilter
	 * @param string $actionFilter
	 * @return array with cache, cacheIdendifier, action, timestamp, timedifference
	 */
	public function getNextLog( $previousNr, $cacheFilter = NULL, $actionFilter = NULL  ) {
		while(TRUE) {
			$log = $this->getLastLog();
			if (is_array($log) && $log['nr'] != $previousNr && 
				( is_null($cacheFilter) || $cacheFilter == $log['cache']) &&
				( is_null($actionFilter) || $actionFilter == $log['action']) 
				) {				
				return $log;
			}
		}	
	}
	
	/**
	 * Gets log from communicationRessource. Depending on the mode this call might be blocking.
	 * 
	 * @return array
	 */
	public function getLastLog() {
		switch ($this->communicationMode) {
			case 'msg':
				$msgtype = NULL;
				$data = NULL;
				$result = msg_receive($this->communicationRessourceHandle, 1, $msgtype, 1000, $data);
			break;
			case 'shmop':
				$data = shmop_read($this->communicationRessourceHandle,0,1000);
				if (!empty($data)) {
					$data = unserialize($data);
				}
			break;
			case 'shm':
				$data = shm_get_var($this->communicationRessourceHandle,1);
				if (!empty($data)) {
					$data = unserialize($data);
				}
			break;
		}
		
		if (empty($data)) {
			return array();
		}	
		else {
			return $data;
		}
	}

	
}
?>