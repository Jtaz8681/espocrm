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

use Espo\Core\Utils\Metadata;
use Espo\Tools\Layout\LayoutProvider;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\InjectableFactory;

use Espo\Core\Utils\Resource\FileReader;
use Espo\Core\Utils\Resource\FileReader\Params as FileReaderParams;

class LayoutTest extends \PHPUnit\Framework\TestCase
{
    /** @var LayoutProvider */
    private $layout;
    /** @var InjectableFactory */
    private $injectableFactory;
    /** @var FileManager */
    private $fileManager;
    private $fileReader;

    protected function setUp(): void
    {
        $this->fileManager = $this->createMock(FileManager::class);
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->fileReader = $this->createMock(FileReader::class);

        $metadata = $this->createMock(Metadata::class);

        $this->layout = new LayoutProvider(
            $this->fileManager,
            $this->injectableFactory,
            $metadata,
            $this->fileReader
        );
    }

    public function testGet1(): void
    {
        $this->fileReader
            ->expects($this->once())
            ->method('exists')
            ->with(
                'layouts/Test/test.json',
                $this->callback(
                    function (FileReaderParams $params): bool {
                        return $params->getScope() === 'Test';
                    }
                )
            )
            ->willReturn(true);

        $this->fileReader
            ->expects($this->once())
            ->method('read')
            ->with(
                'layouts/Test/test.json',
                $this->callback(
                    function (FileReaderParams $params): bool {
                        return $params->getScope() === 'Test';
                    }
                )
            )
            ->willReturn('["test"]');

        $result = $this->layout->get('Test', 'test');

        $this->assertEquals('["test"]', $result);
    }

    public function testGetDefault(): void
    {
        $this->fileReader
            ->expects($this->once())
            ->method('exists')
            ->with(
                'layouts/Test/test.json',
                $this->callback(
                    function (FileReaderParams $params): bool {
                        return $params->getScope() === 'Test';
                    }
                )
            )
            ->willReturn(false);

        $this->fileManager
            ->expects($this->once())
            ->method('isFile')
            ->with('application/Espo/Resources/defaults/layouts/test.json')
            ->willReturn(true);

        $this->fileManager
            ->expects($this->once())
            ->method('getContents')
            ->with('application/Espo/Resources/defaults/layouts/test.json')
            ->willReturn('["test"]');

        $result = $this->layout->get('Test', 'test');

        $this->assertEquals('["test"]', $result);
    }
}
