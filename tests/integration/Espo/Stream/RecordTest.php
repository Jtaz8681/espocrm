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

namespace tests\integration\Espo\Stream;

use Espo\Core\Acl\Table;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Select\SearchParams;
use Espo\Entities\Note;
use Espo\Entities\Portal;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\Tools\Stream\RecordService;
use tests\integration\Core\BaseTestCase;

class RecordTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testPortal(): void
    {
        $em = $this->getEntityManager();

        $portal = $em->createEntity(Portal::ENTITY_TYPE, ['name' => 'Test']);

        $portalUser = $this->createUser([
            'userName' => 'test',
            'portalsIds' => [$portal->getId()],
        ], [
            'data' => [
                CaseObj::ENTITY_TYPE => [
                    Table::ACTION_READ => Table::LEVEL_OWN,
                    Table::ACTION_STREAM => Table::LEVEL_OWN,
                ]
            ],
        ], isPortal: true);

        $case = $em->createEntity(CaseObj::ENTITY_TYPE, [
            'name' => 'Test',
        ], [SaveOption::CREATED_BY_ID => $portalUser->getId(), SaveOption::SILENT => true]);

        $note = $em->getRDBRepositoryByClass(Note::class)->getNew();
        $note->setParent($case);
        $note->setType(Note::TYPE_POST);
        $em->saveEntity($note);

        $notePinned = $em->getRDBRepositoryByClass(Note::class)->getNew();
        $notePinned->setParent($case);
        $notePinned->setType(Note::TYPE_POST);
        $notePinned->setIsPinned(true);
        $em->saveEntity($notePinned);

        $noteInternal = $em->getRDBRepositoryByClass(Note::class)->getNew();
        $noteInternal->setParent($case);
        $noteInternal->setType(Note::TYPE_POST);
        $noteInternal->setIsInternal(true);
        $em->saveEntity($noteInternal);

        $noteInternalPinned = $em->getRDBRepositoryByClass(Note::class)->getNew();
        $noteInternalPinned->setParent($case);
        $noteInternalPinned->setType(Note::TYPE_POST);
        $noteInternalPinned->setIsPinned(true);
        $noteInternalPinned->setIsInternal(true);
        $em->saveEntity($noteInternalPinned);

        $this->auth(
            userName: 'test',
            portalId: $portal->getId(),
        );

        $app = $this->createApplication(
            portalId: $portal->getId(),
            reuse: true,
        );

        $this->setApplication($app);

        $service = $this->getInjectableFactory()->create(RecordService::class);

        $collection = $service->find(CaseObj::ENTITY_TYPE, $case->getId(), SearchParams::create())->getCollection();
        $pinnedCollection = $service->getPinned(CaseObj::ENTITY_TYPE, $case->getId());

        $this->assertCount(2, $collection);
        $this->assertEquals($note->getId(), $collection[1]->getId());
        $this->assertEquals($notePinned->getId(), $collection[0]->getId());

        $this->assertCount(1, $pinnedCollection);
        $this->assertEquals($notePinned->getId(), $collection[0]->getId());
    }
}
