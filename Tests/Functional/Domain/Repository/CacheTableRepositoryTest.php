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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class CacheTableRepositoryTest extends FunctionalTestCase
{

    /**
     * @var CacheTableRepository
     */
    protected $subject;

    public function setUp(): void
    {
        parent::setUp();
        $objectManger = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $objectManger->get(CacheTableRepository::class);
    }

    /**
     * @test
     */
    public function countRowsInTable()
    {
        if ($this->isTYPO3ngt10()) {
            $this->markTestSkipped('This test is only valid for TYPO3 8 and 9 LTS');
        }

        $this->importDataSet(__DIR__ . '/Fixtures/cf_cache_pages.xml');
        $this->assertEquals(
            2,
            $this->subject->countRowsInTable('cf_cache_pages')
        );
    }

    /**
     * @test
     */
    public function countRowsInTableV10()
    {
        if (!$this->isTYPO3gte10()) {
            $this->markTestSkipped('This test is only valid for TYPO3 10 or greater');
        }

        $this->importDataSet(__DIR__ . '/Fixtures/cache_pages.xml');
        $this->assertEquals(
            2,
            $this->subject->countRowsInTable('cache_pages')
        );
    }

    private function isTYPO3ngt10(): bool
    {
        if ($this->getTypo3MajorVersion() < 10) {
            return false;
        }
        return true;
    }

    private function isTYPO3gte10(): bool
    {
        if ($this->getTypo3MajorVersion() >= 10) {
            return true;
        }
        return false;
    }

    private function getTypo3MajorVersion(): int
    {
        if (class_exists(Typo3Version::class)) {
            return (new Typo3Version())->getMajorVersion();
        }
        throw new \Exception('TYPO3 Version could not be determined');
    }
}
