<?php
namespace Aoe\Cachemgm\Cache\Frontend;

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

use Aoe\Cachemgm\Cache\MemoryLogWriter;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A cache frontend for any kinds of PHP variables that writes into cache log
 *
 * @package TYPO3
 * @author Daniel Pötzinger
 * @api
 * @scope prototype
 */
class LogablePhpFrontend extends PhpFrontend {
	
	/**
	 * @var MemoryLogWriter
	 */
	protected $cacheLog;

	/**
	 * Initializes this cache frontend
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->cacheLog = GeneralUtility::makeInstance(MemoryLogWriter::class);
	}

	/**
	 * Saves the PHP source code in the cache.
	 *
	 * @param string $entryIdentifier An identifier used for this cache entry, for example the class name
	 * @param string $sourceCode PHP source code
	 * @param array $tags Tags to associate with this cache entry
	 * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
	 * @return void
	 * @throws \InvalidArgumentException If $entryIdentifier or $tags is invalid
	 * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException If $sourceCode is not a string
	 * @api
	 */
	public function set($entryIdentifier, $sourceCode, array $tags = array(), $lifetime = NULL) {
		$startTime = $this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_SETSTART);
		$result = parent::set($entryIdentifier, $variable,  $tags , $lifetime );
		$this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_SETEND, $startTime);
		return $result;
	}
		
	/**
	 * Loads PHP code from the cache and require_onces it right away.
	 *
	 * @param string $entryIdentifier An identifier which describes the cache entry to load
	 * @return mixed Potential return value from the include operation
	 * @api
	 */
	public function requireOnce($entryIdentifier) {
		$startTime = $this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_REQOSTART);
		$result = parent::requireOnce($entryIdentifier);
		$this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_REQOEND, $startTime);
		return $result;
	}
	
	/**
	 * Finds and returns a variable value from the cache.
	 *
	 * @param string $entryIdentifier Identifier of the cache entry to fetch
	 * @return mixed The value
	 * @throws \InvalidArgumentException if the identifier is not valid
	 * @api
	 */
	public function get($entryIdentifier) {
		$startTime = $this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_GETSTART);
		$result = parent::get($entryIdentifier, $variable,  $tags , $lifetime );
		if ($result !== false) {
			$this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_HIT, $startTime);
		}
		else {
			$this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_MISS, $startTime);
		}		
		return $result;
	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string $entryIdentifier An identifier specifying the cache entry
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 * @throws \InvalidArgumentException If $entryIdentifier is invalid
	 * @api
	 */
	public function has($entryIdentifier) {
		$startTime = $this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_HASSTART);
		$result = parent::get($entryIdentifier, $variable,  $tags , $lifetime );
		if ($result !== false) {
			$this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_HASHIT, $startTime);
		}
		else {
			$this->cacheLog->log($this->getIdentifier(),$entryIdentifier,MemoryLogWriter::ACTION_HASMISS, $startTime);
		}		
		return $result;
	}
}
