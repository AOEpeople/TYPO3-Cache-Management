<?php
namespace Aoe\Cachemgm\Tests\Functional\Cache\Frontend;

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

use Aoe\Cachemgm\Cache\Frontend\LogablePhpFrontend;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class LogablePhpFrontendTest extends FunctionalTestCase
{

    /**
     * @var LogablePhpFrontend
     */
    protected $subject;

    protected $testExtensionsToLoad = ['typo3conf/ext/cachemgm'];

    protected function setUp()
    {
        parent::setUp();
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $cacheBackend = new FileBackend('Functional-Tests');
        $this->subject = $objectManager->get(LogablePhpFrontend::class, 'Functional-Tests', $cacheBackend);
    }

    /**
     * @test
     * @expectedException \TYPO3\CMS\Core\Cache\Exception\InvalidDataException
     */
    public function setThrowsExceptionBecauseOfNullSourceCode()
    {
        // The cacheIdentifier persists and therefor it needs to be unique for the tests
        $cacheIdentifier = sha1(time());
        $this->subject->set(
            $cacheIdentifier,
            null
        );
    }

    /**
     * @test
     */
    public function setAndGetCache()
    {
        $cacheIdentifier = sha1(time());
        $sourceCode = 'phpinfo();';
        $this->subject->set(
            $cacheIdentifier,
            $sourceCode
        );

        $this->assertStringStartsWith(
            '<?php',
            $this->subject->get($cacheIdentifier)
        );
    }

    /**
     * @test
     */
    public function HasCache()
    {
        $cacheIdentifier = sha1(time());

        $this->assertFalse($this->subject->has($cacheIdentifier));

        $sourceCode = 'not_import_function_name();';
        $this->subject->set(
            $cacheIdentifier,
            $sourceCode
        );

        $this->assertStringStartsWith(
            '<?php',
            $this->subject->has($cacheIdentifier)
        );
    }

    /**
     * @test
     */
    public function requireOnceReturnResult()
    {
        $cacheIdentifier = sha1(time());
        $dateYear = date('Y');
        $sourceCode = "echo date('Y');";
        $this->subject->set(
            $cacheIdentifier,
            $sourceCode
        );

        $this->subject->requireOnce($cacheIdentifier);
        $this->expectOutputString($dateYear);
    }
}
