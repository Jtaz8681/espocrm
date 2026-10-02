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

namespace tests\unit\Espo\Tools\DashboardTemplate;

use Espo\Classes\FieldDuplicators\DashboardTemplate\Layout as LayoutFieldDuplicator;
use Espo\Entities\DashboardTemplate;
use PHPUnit\Framework\TestCase;

class DuplicateTest extends TestCase
{
    public function testDuplicate(): void
    {
        $entity = $this->createMock(DashboardTemplate::class);

        $original = [
            (object) [
                'id' => 'tab-01',
                'layout' => [
                    (object) [
                        'id' => 'd-01',
                    ]
                ],
            ],
        ];

        $originalOptions = (object) [
            'd-01' => (object) ['k' => 'v'],
        ];

        $entity->method('getLayoutRaw')
            ->willReturn($original);

        $entity->method('getDashletsOptionsRaw')
            ->willReturn($originalOptions);

        $duplicator = new LayoutFieldDuplicator();

        $values = $duplicator->duplicate($entity, DashboardTemplate::FIELD_LAYOUT);

        $copy = $values->{DashboardTemplate::FIELD_LAYOUT} ?? null;
        $copyOptions = $values->{DashboardTemplate::FIELD_DASHLETS_OPTIONS} ?? null;

        $this->assertIsArray($copy);
        $this->assertIsString($copy[0]->id);
        $this->assertNotEquals($original[0]->id, $copy[0]->id);
        $this->assertNotEquals($original[0]->layout[0]->id, $copy[0]->layout[0]->id);
        $this->assertIsString($copy[0]->layout[0]->id);
        $this->assertFalse(property_exists($copyOptions, 'd-01'));
        $this->assertCount(1, get_object_vars($copyOptions));
    }
}
