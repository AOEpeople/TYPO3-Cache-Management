<?php

namespace Aoe\Cachemgm\Tests\Unit\Cache;

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

use Aoe\Cachemgm\Cache\MemoryLogWriter;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class MemoryLogWriterTest extends UnitTestCase
{

    /**
     * @var MemoryLogWriter
     */
    protected $subject;

    public function setUp()
    {
        parent::setUp();
        $this->subject = GeneralUtility::makeInstance(MemoryLogWriter::class);
    }

    /**
     * @test
     */
    public function expectLogEnabledByDefault()
    {
        $this->assertTrue(
            $this->subject->isEnabled()
        );
    }

    /**
     * @test
     */
    public function setEnabled()
    {
        $this->subject->setEnable(false);
        $this->assertFalse(
            $this->subject->isEnabled()
        );

        $this->subject->setEnable(true);
        $this->assertTrue(
            $this->subject->isEnabled()
        );
    }

    /**
     * @test
     */
    public function log()
    {
        $cacheId = '123456';
        $cacheIdentifier = sha1(time());
        $cacheAction = 'Action';

        $this->assertInternalType(
            'float',
            $this->subject->log(
                $cacheId,
                $cacheIdentifier,
                $cacheAction
            )
        );
    }


}
