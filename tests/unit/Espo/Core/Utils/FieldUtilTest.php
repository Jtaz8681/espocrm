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

use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use PHPUnit\Framework\TestCase;

class FieldUtilTest extends TestCase
{
    private ?Metadata $metadata = null;

    protected function setUp(): void
    {
        $this->metadata = $this->createMock(Metadata::class);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function initMetadata(array $data): void
    {
        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($key, $default) use ($data) {
                return Util::getValueByKey($data, $key, $default);
            });
    }

    public function testGetActualAttributeListSuffix(): void
    {
        $this->initMetadata([
            'fields' => [
                'testType' => [
                    'naming' => 'suffix',
                    'actualFields' => [
                        '',
                        'helloOne',
                    ],
                ],
            ],
            'entityDefs' => [
                'Test' => [
                    'fields' => [
                        'test' => [
                            'type' => 'testType',
                            'additionalAttributeList' => ['helloTwo'],
                            'fullNameAdditionalAttributeList' => ['helloThree'],
                        ]
                    ]
                ]
            ]
        ]);

        $fieldUtil = new FieldUtil($this->metadata);

        $actual = $fieldUtil->getActualAttributeList('Test', 'test');

        $this->assertEquals([
            'test',
            'testHelloOne',
            'testHelloTwo',
            'helloThree',
        ], $actual);
    }

    public function testGetActualAttributeListPrefix(): void
    {
        $this->initMetadata([
            'fields' => [
                'testType' => [
                    'naming' => 'prefix',
                    'actualFields' => [
                        '',
                        'helloOne',
                    ],
                ],
            ],
            'entityDefs' => [
                'Test' => [
                    'fields' => [
                        'test' => [
                            'type' => 'testType',
                            'additionalAttributeList' => ['helloTwo'],
                            'fullNameAdditionalAttributeList' => ['helloThree'],
                        ]
                    ]
                ]
            ]
        ]);

        $fieldUtil = new FieldUtil($this->metadata);

        $actual = $fieldUtil->getActualAttributeList('Test', 'test');

        $this->assertEquals([
            'test',
            'helloOneTest',
            'helloTwoTest',
            'helloThree',
        ], $actual);
    }
}
