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

namespace tests\integration\Espo\Tools\Merge;

use Espo\Core\Action\Actions\Merge\Merger;
use Espo\Core\Action\Params;
use Espo\Core\Field\DateTimeOptional;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Meeting;
use tests\integration\Core\BaseTestCase;

class MergeTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testMerge(): void
    {
        $em = $this->getEntityManager();

        $lead1 = $em->getRepositoryByClass(Lead::class)->getNew();
        $lead1->setLastName('L 1');
        $em->saveEntity($lead1);

        $lead2 = $em->getRepositoryByClass(Lead::class)->getNew();
        $lead2->setLastName('L 2');
        $em->saveEntity($lead2);

        $meeting = $em->getRepositoryByClass(Meeting::class)->getNew();
        $meeting->setParent($lead2);
        $meeting->setName('M');
        $meeting->setDateStart(DateTimeOptional::createNow());
        $em->saveEntity($meeting);

        $merger = $this->getInjectableFactory()->create(Merger::class);

        $merger->process(
            params: new Params(Lead::ENTITY_TYPE, $lead1->getId()),
            sourceIdList: [$lead2->getId()],
            data: (object) [],
        );

        $em->refreshEntity($meeting);

        $this->assertEquals($lead1->getId(), $meeting->getParent()?->getId());
        $this->assertEquals(Lead::ENTITY_TYPE, $meeting->getParent()?->getEntityType());

        $this->assertNull(
            $em->getEntityById(Lead::ENTITY_TYPE, $lead2->getId())
        );
    }
}
