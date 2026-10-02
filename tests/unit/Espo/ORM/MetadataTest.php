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

namespace tests\unit\Espo\ORM;

use Espo\ORM\{
    Metadata,
    MetadataDataProvider,
};

class MetadataTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {

    }

    public function testHas1()
    {
        $metadata = $this->createMetadata([
            'Test' => [],
        ]);

        $this->assertTrue($metadata->has('Test'));
    }

    public function testHas2()
    {
        $metadata = $this->createMetadata([
            'Test' => [],
        ]);

        $this->assertFalse($metadata->has('Hello'));
    }

    public function testGet1()
    {
        $metadata = $this->createMetadata([
            'Test' => [
                'indexes' => [],
            ],
        ]);

        $this->assertEquals([], $metadata->get('Test', 'indexes'));
    }

    public function testGet2()
    {
        $metadata = $this->createMetadata([
            'Test' => [
                'relations' => [
                    'test' => [
                        'type' => 'hasMany',
                    ],
                ],
            ],
        ]);

        $this->assertEquals('hasMany', $metadata->get('Test', 'relations.test.type'));
    }

    public function testGet3()
    {
        $metadata = $this->createMetadata([
            'Test' => [
                'relations' => [
                    'test' => [
                        'type' => 'hasMany',
                    ],
                ],
            ],
        ]);

        $this->assertEquals('hasMany', $metadata->get('Test', ['relations', 'test', 'type']));
    }

    protected function createMetadata(array $data) : Metadata
    {
        $metadataDataProvider = $this->createMock(MetadataDataProvider::class);

        $metadataDataProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn($data);

        return new Metadata($metadataDataProvider);
    }
}
