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

use Aoe\Cachemgm\Cache\MemoryLogReader;
use Aoe\Cachemgm\Cache\MemoryLogWriter;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class MemoryLogReaderTest extends UnitTestCase
{
    /**
     * @var MemoryLogReader
     */
    protected $subject;

    /**
     * @var MemoryLogWriter
     */
    protected $logWriter;

    public function setUp()
    {
        parent::setUp();
        $this->subject = new MemoryLogReader();
        $this->logWriter = new MemoryLogWriter();
    }

    /**
     * @test
     */
    public function setAndGetProcessId()
    {
        $processId = 5678;
        $this->subject->setProcessId($processId);

        $this->assertEquals(
            $processId,
            $this->subject->getProcessId()
        );
    }

    /**
     * @test
     */
    public function getLastLogReturnLogMessage()
    {
        $processId = 9876;
        $cacheId = '123456';
        $time = time();

        $cacheIdentifier = sha1($time);
        $cacheAction = 'Action';

        $this->subject->setProcessId($processId);

        $result = $this->subject->log($cacheId, $cacheIdentifier, $cacheAction);
        $this->assertInternalType(
            'float',
            $result
        );

        $expectedArray = [
            'cache' => $cacheId,
            'id' => $cacheIdentifier,
            'action' => $cacheAction,
            'pid' => $processId,
            'nr' => '',
        ];

        $actualArray = $this->subject->getLastLog();

        // Testing timestamp separately as we cannot control the timestamp, only check that it is a float
        $this->assertInternalType('float', $actualArray['timestamp']);
        $this->assertGreaterThan($time - 2000, $actualArray['timestamp']);
        $this->assertLessThan($time + 2000, $actualArray['timestamp']);

        // unset the timestamp to check the rest as an array
        unset($actualArray['timestamp']);

        $this->assertInternalType(
            'array',
            $actualArray
        );

        $this->assertEquals(
            $expectedArray,
            $actualArray
        );
    }

    /**
     * @test
     */
    public function getNextLog()
    {
        $this->subject->log('tomas', 'hest', 'hyphyp');

        $this->assertEquals(
            [],
            $this->subject->getNextLog(5)
        );
    }

}
