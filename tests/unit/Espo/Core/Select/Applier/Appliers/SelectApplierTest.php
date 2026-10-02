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

namespace tests\unit\Espo\Core\Select\Applier\Appliers;

use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Select\Applier as SelectApplier;
use Espo\Core\Select\Select\MetadataProvider;
use Espo\Core\Utils\FieldUtil;

use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use PHPUnit\Framework\TestCase;

class SelectApplierTest extends TestCase
{
    private $metadataProvider;
    private $queryBuilder;
    private $applier;
    private $entityType;
    private $fieldUtil;
    private $searchParams;
    private $user;

    protected function setUp(): void
    {
        $this->user = $this->createMock(User::class);
        $this->metadataProvider = $this->createMock(MetadataProvider::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->searchParams = $this->createMock(SearchParams::class);
        $this->fieldUtil = $this->createMock(FieldUtil::class);

        $this->entityType = 'Test';

        $this->applier = new SelectApplier(
            $this->entityType,
            $this->user,
            $this->fieldUtil,
            $this->metadataProvider
        );
    }

    public function testApply1()
    {
        $this->initTestApply();
    }

    public function testApplyPortal()
    {
        $this->initTestApply(true);
    }

    protected function initTestApply(bool $isPortal = false)
    {
        $select = ['testSelect', 'testNotExisting', 'testText'];

        $orderBy = 'testOrder';

        $aclAttributeList = ['testAcl'];

        $aclPortalAttributeList = ['testAclPortal'];

        $dependencyMap = [
            'testSelect' => [
                'testDependency',
            ],
        ];

        $aclAttribute = $aclAttributeList[0];

        if ($isPortal) {
            $aclAttribute = $aclPortalAttributeList[0];
        }

        $this->user
            ->expects($this->any())
            ->method('isPortal')
            ->willReturn($isPortal);

        $this->searchParams
            ->expects($this->any())
            ->method('getMaxTextAttributeLength')
            ->willReturn(100);

        $this->searchParams
            ->expects($this->any())
            ->method('getSelect')
            ->willReturn($select);

        $this->searchParams
            ->expects($this->any())
            ->method('getOrderBy')
            ->willReturn($orderBy);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getAclAttributeList')
            ->willReturn($aclAttributeList);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getAclPortalAttributeList')
            ->willReturn($aclPortalAttributeList);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getSelectAttributesDependencyMap')
            ->with($this->entityType)
            ->willReturn($dependencyMap);

        $this->fieldUtil
            ->expects($this->once())
            ->method('getAttributeList')
            ->with($this->entityType, $orderBy)
            ->willReturn([$orderBy]);

        $this->metadataProvider
            ->expects($this->any())
            ->method('hasAttribute')
            ->willReturnMap(
                    [
                        [$this->entityType, 'id', true],
                        [$this->entityType, 'testSelect', true],
                        [$this->entityType, 'testNotExisting', false],
                        [$this->entityType, 'testOrder', true],
                        [$this->entityType, 'testAcl', true],
                        [$this->entityType, 'testAclPortal', true],
                        [$this->entityType, 'testDependency', true],
                        [$this->entityType, 'testText', true],
                    ]
            );

        $this->metadataProvider
            ->expects($this->any())
            ->method('isAttributeNotStorable')
            ->willReturn(false);

        $this->metadataProvider
            ->expects($this->any())
            ->method('getAttributeType')
            ->willReturnMap(
                [
                    [$this->entityType, 'id', Entity::ID],
                    [$this->entityType, 'testSelect', Entity::VARCHAR],
                    [$this->entityType, 'testOrder', Entity::VARCHAR],
                    [$this->entityType, 'testAcl', Entity::VARCHAR],
                    [$this->entityType, 'testAclPortal', Entity::VARCHAR],
                    [$this->entityType, 'testDependency', Entity::VARCHAR],
                    [$this->entityType, 'testText', Entity::TEXT],
                ]
            );

        $expected = [
            'id',
            $aclAttribute,
            'testSelect',
            ['LEFT:(testText, 100)', 'testText'],
            'testOrder',
            'testDependency',
        ];

        $this->queryBuilder
            ->expects($this->once())
            ->method('select')
            ->with($expected);

        $this->applier->apply($this->queryBuilder, $this->searchParams);
    }
}
