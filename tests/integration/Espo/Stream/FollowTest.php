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

namespace integration\Espo\Stream;

use Espo\Core\Acl\Table;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Tools\EntityManager\EntityManager;
use Espo\Tools\Stream\Service;
use tests\integration\Core\BaseTestCase;

class FollowTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testFollow(): void
    {
        $roleData = [
            Account::ENTITY_TYPE => [
                'create' => Table::LEVEL_NO,
                'read' => Table::LEVEL_ALL,
                'stream' => Table::LEVEL_ALL,
            ],
            Contact::ENTITY_TYPE => [
                'create' => Table::LEVEL_NO,
                'read' => Table::LEVEL_ALL,
                'stream' => Table::LEVEL_ALL,
            ],
            Meeting::ENTITY_TYPE => [
                'create' => Table::LEVEL_NO,
                'read' => Table::LEVEL_ALL,
                'stream' => Table::LEVEL_ALL,
            ],
        ];

        $user1 = $this->createUser('test-1', [
            'data' => $roleData,
        ]);

        $user2 = $this->createUser('test-2', [
            'data' => $roleData,
        ]);

        $user3 = $this->createUser('test-3', [
            'data' => $roleData,
        ]);

        $tool = $this->getInjectableFactory()->create(EntityManager::class);

        /** @noinspection PhpArrayKeyDoesNotMatchArrayShapeInspection */
        $tool->update(Contact::ENTITY_TYPE, ['assignedUsers' => true]);

        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $streamService = $this->getInjectableFactory()->create(Service::class);

        $em = $this->getEntityManager();

        //

        $account = $em->getRepositoryByClass(Account::class)->getNew();
        $account->setAssignedUser($user1);
        $em->saveEntity($account);

        $this->assertTrue($streamService->checkIsFollowed($account, $user1->getId()));

        $account->setAssignedUser($user2);
        $em->saveEntity($account);

        $this->assertTrue($streamService->checkIsFollowed($account, $user2->getId()));

        //

        $contact = $em->getRepositoryByClass(Contact::class)->getNew();
        $contact->setLinkMultipleIdList(Field::ASSIGNED_USERS, [$user1->getId()]);
        $em->saveEntity($contact);

        $this->assertTrue($streamService->checkIsFollowed($contact, $user1->getId()));

        $contact->setLinkMultipleIdList(Field::ASSIGNED_USERS, [$user2->getId()]);
        $em->saveEntity($contact);

        $this->assertTrue($streamService->checkIsFollowed($contact, $user2->getId()));

        //

        $meeting = $em->getRepositoryByClass(Meeting::class)->getNew();
        $meeting->setAssignedUser($user1);
        $meeting->setUsers(LinkMultiple::create()->withAddedId($user2->getId()));

        $em->saveEntity($meeting);

        $this->assertTrue($streamService->checkIsFollowed($meeting, $user1->getId()));
        $this->assertTrue($streamService->checkIsFollowed($meeting, $user2->getId()));

        $meeting->setUsers(LinkMultiple::create()->withAddedId($user3->getId()));

        $em->saveEntity($meeting);

        $this->assertTrue($streamService->checkIsFollowed($meeting, $user3->getId()));
    }
}
