<?php
declare(strict_types=1);

namespace Aoe\Cachemgm\Test\Unit\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use Aoe\Cachemgm\Utility\CacheUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class CacheUtilityTest extends UnitTestCase
{
    public function setUp(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] = [
            'cache_core' => [
                'frontend' => 'TYPO3\CMS\Core\Cache\Frontend\PhpFrontend',
                'backend' => 'TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend',
                'options' => ''
            ],
            'cache_hash' => [
                'frontend' => 'TYPO3\CMS\Core\Cache\Frontend\PhpFrontend',
                'backend' => 'TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend',
                'options' => [
                    'first' => true,
                    'second' => 1234,
                ]
            ],
            'no_caches_defined' => []
        ];
    }

    /**
     * @test
     */
    public function getAvailableCaches_returnsArray()
    {
        $this->assertTrue(is_array(CacheUtility::getAvailableCaches()));
    }

    /**
     * @test
     */
    public function getCacheType_returnsString()
    {
        $this->assertEquals(
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_core']['frontend'],
            CacheUtility::getCacheType('cache_core')
        );
    }

    /**
     * @test
     */
    public function getCacheType_returnsDefaultValue()
    {
        $this->assertEquals(
            'Default (Variable)',
            CacheUtility::getCacheType('no_caches_defined')
        );
    }

    /**
     * @test
     */
    public function getCacheBackend_returnsString()
    {
        $this->assertEquals(
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_core']['backend'],
            CacheUtility::getCacheBackendType('cache_core')
        );
    }

    /**
     * @test
     */
    public function getCacheBackend_returnsDefaultValue()
    {
        $this->assertEquals(
            'Default (DbBackend)',
            CacheUtility::getCacheBackendType('no_caches_defined')
        );
    }


    /**
     * @test
     */
    public function getCacheOptions_returnsEmptyString()
    {
        $this->assertEmpty(CacheUtility::getCacheOptions('cache_core'));
    }

    /**
     * @test
     */
    public function getCacheOptions_returnsOptionString()
    {
        $cacheOptions = CacheUtility::getCacheOptions('cache_hash');
        $this->assertStringContainsString('first', $cacheOptions);
        $this->assertStringContainsString('second', $cacheOptions);
    }
}
