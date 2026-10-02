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

namespace tests\unit\Espo\Core\Utils\File;

use Espo\Core\Utils\File\MimeType;
use Espo\Core\Utils\Metadata;

class MimeTypeTest extends \PHPUnit\Framework\TestCase
{
    private Metadata $metadata;

    protected function setUp(): void
    {
        $this->metadata = $this->createMock(Metadata::class);
    }

    public function testGetMimeTypeByExtension(): void
    {
        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->with(['app', 'file', 'extensionMimeTypeMap', 'csv'])
            ->willReturn(['text/csv']);

        $util = new MimeType($this->metadata);

        $this->assertEquals('text/csv', $util->getMimeTypeByExtension('csv'));
        $this->assertEquals('text/csv', $util->getMimeTypeByExtension('CSV'));
    }

    public function testMatchMimeTypeToAcceptToken(): void
    {
        $this->assertTrue(MimeType::matchMimeTypeToAcceptToken('text/csv', 'text/csv'));
        $this->assertFalse(MimeType::matchMimeTypeToAcceptToken('text/csv', 'text/plain'));
        $this->assertTrue(MimeType::matchMimeTypeToAcceptToken('video/mpeg', 'video/*'));
        $this->assertFalse(MimeType::matchMimeTypeToAcceptToken('video/mpeg', 'image/*'));
    }
}
