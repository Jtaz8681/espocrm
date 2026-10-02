<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

namespace tests\unit\Espo\Core\Utils;

use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager as FileManager;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use InvalidArgumentException;

class DataCacheTest extends TestCase
{
    private $fileManager;
    private $dataCache;

    protected function setUp() : void
    {
        $this->fileManager = $this->createMock(FileManager::class);

        $this->dataCache = new DataCache($this->fileManager);
    }

    public function testHasTrue()
    {
        $this->fileManager
            ->expects($this->once())
            ->method('isFile')
            ->with('data/cache/application/autoload.php')
            ->willReturn(true);

        $result = $this->dataCache->has('autoload');

        $this->assertTrue($result);
    }

    public function testHasFalse()
    {
        $this->fileManager
            ->expects($this->once())
            ->method('isFile')
            ->with('data/cache/application/autoload.php')
            ->willReturn(false);

        $result = $this->dataCache->has('autoload');

        $this->assertFalse($result);
    }

    public function testStoreData()
    {
        $data = [
            'test' => 1,
        ];

        $this->fileManager
            ->expects($this->once())
            ->method('putPhpContents')
            ->with('data/cache/application/autoload.php', $data, true, true)
            ->willReturn(true);

        $this->dataCache->store('autoload', $data);
    }

    public function testStoreError()
    {
        $this->expectException(RuntimeException::class);

        $data = [
            'test' => 1,
        ];

        $this->fileManager
            ->expects($this->once())
            ->method('putPhpContents')
            ->with('data/cache/application/autoload.php', $data, true, true)
            ->willReturn(false);

        $this->dataCache->store('autoload', $data);
    }

    public function testStoreBadDataType()
    {
        $this->expectException(InvalidArgumentException::class);

        $data = false;

        $this->dataCache->store('autoload', $data);
    }

    public function testSubDir()
    {
        $this->fileManager
            ->expects($this->once())
            ->method('isFile')
            ->with('data/cache/application/languageTest/test0.php')
            ->willReturn(true);

        $result = $this->dataCache->has('languageTest/test0');

        $this->assertTrue($result);
    }

    public function testBadKey1()
    {
        $this->expectException(InvalidArgumentException::class);

        $result = $this->dataCache->has('/language');

        $this->assertTrue($result);
    }

    public function testBadKey2()
    {
        $this->expectException(InvalidArgumentException::class);

        $result = $this->dataCache->has('language/');

        $this->assertTrue($result);
    }

    public function testBadKey3()
    {
        $this->expectException(InvalidArgumentException::class);

        $result = $this->dataCache->has('');

        $this->assertTrue($result);
    }

    public function testBadKey4()
    {
        $this->expectException(InvalidArgumentException::class);

        $result = $this->dataCache->has('language\test');

        $this->assertTrue($result);
    }
}
