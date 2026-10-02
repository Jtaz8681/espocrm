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

namespace tests\integration\Espo\Record;

use Espo\Core\Record\ServiceContainer;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\Modules\Crm\Entities\Task;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;
use tests\integration\Core\BaseTestCase;

class CascadeRemovalTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testRemovalAndRestoral(): void
    {
        $metadata = $this->getMetadata();
        $metadata->set('entityDefs', 'Account', [
            'links' => [
                'opportunities' => [
                    RelationParam::CASCADE_REMOVAL => true,
                ]
            ]
        ]);
        $metadata->set('entityDefs', 'Opportunity', [
            'links' => [
                'tasks' => [
                    RelationParam::CASCADE_REMOVAL => true,
                ]
            ]
        ]);
        $metadata->save();
        $this->getDataManager()->clearCache();
        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $em = $this->getEntityManager();

        $account = $em->createEntity('Account', ['name' => 'a1']);
        $contact = $em->createEntity('Contact', ['lastName' => 'c1', 'accountId' => $account->getId()]);
        $opp1 = $em->createEntity('Opportunity', ['name' => 'o1', 'accountId' => $account->getId()]);
        $opp2 = $em->createEntity('Opportunity', ['name' => 'o2', 'accountId' => $account->getId()]);
        $opp3 = $em->createEntity('Opportunity', ['name' => 'o3']);

        $task1 = $em->createEntity('Task', [
            'name' => 't1',
            'parentType' => 'Opportunity',
            'parentId' => $opp1->getId(),
        ]);

        $em->removeEntity($account);

        $this->assertNotNull(
            $em->getRDBRepository(Account::ENTITY_TYPE)
                ->clone(
                    SelectBuilder::create()
                        ->from(Account::ENTITY_TYPE)
                        ->withDeleted()
                        ->build()
                )
                ->where([
                    Attribute::ID => $account->getId(),
                    Attribute::DELETED => true,
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Contact::ENTITY_TYPE)
                ->where([
                    Attribute::ID => $contact->getId(),
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Opportunity::ENTITY_TYPE)
                ->clone(
                    SelectBuilder::create()
                        ->from(Opportunity::ENTITY_TYPE)
                        ->withDeleted()
                        ->build()
                )
                ->where([
                    Attribute::ID => $opp1->getId(),
                    Attribute::DELETED => true,
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Opportunity::ENTITY_TYPE)
                ->clone(
                    SelectBuilder::create()
                        ->from(Opportunity::ENTITY_TYPE)
                        ->withDeleted()
                        ->build()
                )
                ->where([
                    Attribute::ID => $opp2->getId(),
                    Attribute::DELETED => true,
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Task::ENTITY_TYPE)
                ->clone(
                    SelectBuilder::create()
                        ->from(Task::ENTITY_TYPE)
                        ->withDeleted()
                        ->build()
                )
                ->where([
                    Attribute::ID => $task1->getId(),
                    Attribute::DELETED => true,
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Opportunity::ENTITY_TYPE)
                ->where([
                    Attribute::ID => $opp3->getId(),
                ])
                ->findOne()
        );

        //

        $service = $this->getContainer()->getByClass(ServiceContainer::class)->get(Account::ENTITY_TYPE);

        $service->restoreDeleted($account->getId());

        $this->assertNotNull(
            $em->getRDBRepository(Account::ENTITY_TYPE)
                ->where([
                    Attribute::ID => $account->getId(),
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Opportunity::ENTITY_TYPE)
                ->where([
                    Attribute::ID => $opp1->getId(),
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Opportunity::ENTITY_TYPE)
                ->where([
                    Attribute::ID => $opp2->getId(),
                ])
                ->findOne()
        );

        $this->assertNotNull(
            $em->getRDBRepository(Task::ENTITY_TYPE)
                ->where([
                    Attribute::ID => $task1->getId(),
                ])
                ->findOne()
        );
    }
}
