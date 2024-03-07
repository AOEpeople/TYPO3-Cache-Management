<?php

declare(strict_types=1);

namespace Aoe\Cachemgm\Tests\Functional\Domain\Repository;

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

use Aoe\Cachemgm\Domain\Repository\CacheTableRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class CacheTableRepositoryTest extends FunctionalTestCase
{

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    // Set pages cache database backend, testing-framework sets this to NullBackend by default.
                    'pages' => [
                        'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    ],
                ],
            ],
        ],
    ];

    protected array $testExtensionsToLoad = ['typo3conf/ext/cachemgm'];

    /**
     * @var CacheTableRepository
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = GeneralUtility::makeInstance(CacheTableRepository::class);
    }

    public function testCountRowsInTable(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/cache_pages.xml');
        $this->assertSame(
            2,
            $this->subject->countRowsInTable('cache_pages')
        );
    }
}
