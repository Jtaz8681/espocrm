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
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Record\UpdateParams;
use Espo\Core\Select\SearchParams;
use Espo\Entities\Note;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\Tools\Stream\RecordService;
use tests\integration\Core\BaseTestCase;

class AuditTest extends BaseTestCase
{
    public function testAudit1(): void
    {
        $this->createUser('test1', [
            'auditPermission' => Table::LEVEL_YES,
            'data' => [
                KnowledgeBaseArticle::ENTITY_TYPE => [
                    'create' => Table::LEVEL_NO,
                    'read' => Table::LEVEL_ALL,
                ],
            ]
        ]);

        $this->createUser('test2', [
            'auditPermission' => Table::LEVEL_NO,
            'data' => [
                KnowledgeBaseArticle::ENTITY_TYPE => [
                    'create' => Table::LEVEL_NO,
                    'read' => Table::LEVEL_ALL,
                ],
            ]
        ]);

        $this->createUser('test3', [
            'auditPermission' => Table::LEVEL_NO,
            'data' => [
                KnowledgeBaseArticle::ENTITY_TYPE => [
                    'create' => Table::LEVEL_NO,
                    'read' => Table::LEVEL_NO,
                ],
            ]
        ]);

        $this->getMetadata()->set('entityDefs', KnowledgeBaseArticle::ENTITY_TYPE, [
            'fields' => [
                'publishDate' => [
                    'audited' => true,
                ],
            ],
        ]);
        $this->getMetadata()->save();

        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(KnowledgeBaseArticle::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $article = $service->create((object) [
            'name' => 'Test 1',
            'publishDate' => '2025-01-01',
        ], CreateParams::create())->getEntity();

        /** @noinspection PhpUnhandledExceptionInspection */
        $service->update($article->getId(), (object) [
            'publishDate' => '2025-01-02',
        ], UpdateParams::create());

        $em = $this->getEntityManager();

        $note = $em->getRDBRepositoryByClass(Note::class)
            ->where([
                'parentId' => $article->getId(),
                'parentType' => $article->getEntityType(),
                'type' => Note::TYPE_UPDATE,
            ])
            ->findOne();

        $this->assertNotNull($note);
        $this->assertEquals(['publishDate'], $note->getData()?->fields);

        $searchParams = SearchParams::create();

        $this->authenticate('test1');

        $service = $this->getInjectableFactory()->create(RecordService::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $recordCollection = $service->findUpdates(KnowledgeBaseArticle::ENTITY_TYPE, $article->getId(), $searchParams);

        $this->assertCount(1, $recordCollection->getCollection()->getValueMapList());

        $this->authenticate('test2');

        $isThrown = false;

        try {
            $service = $this->getInjectableFactory()->create(RecordService::class);
            /** @noinspection PhpUnhandledExceptionInspection */
            $service->findUpdates(KnowledgeBaseArticle::ENTITY_TYPE, $article->getId(), $searchParams);
        } catch (Forbidden) {
            $isThrown = true;
        }

        $this->assertTrue($isThrown);

        $this->authenticate('test3');

        $isThrown = false;

        try {
            $service = $this->getInjectableFactory()->create(RecordService::class);
            /** @noinspection PhpUnhandledExceptionInspection */
            $service->findUpdates(KnowledgeBaseArticle::ENTITY_TYPE, $article->getId(), $searchParams);
        } catch (Forbidden) {
            $isThrown = true;
        }

        $this->assertTrue($isThrown);
    }
}
