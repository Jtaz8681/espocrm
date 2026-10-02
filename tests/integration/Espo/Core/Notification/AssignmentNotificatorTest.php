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

namespace tests\integration\Espo\Core\Notification;

use Espo\ORM\EntityManager;
use Espo\Core\Utils\Config\ConfigWriter;

use Espo\Core\InjectableFactory;
use Espo\Entities\User;
use tests\integration\Core\BaseTestCase;

class AssignmentNotificatorTest extends BaseTestCase
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var User
     */
    private $user1;

    /**
     * @var User
     */
    private $user2;

    public function setUp(): void
    {
        parent::setUp();

        $this->initTestData();

        $this->entityManager = $this->getEntityManager();
    }

    private function initTestData() : void
    {
        $configWriter = $this->getContainer()->getByClass(InjectableFactory::class)->create(ConfigWriter::class);

        $this->getMetadata()->set('scopes', 'Meeting', [
            'stream' => false,
        ]);

        $this->getMetadata()->save();

        $configWriter->set('assignmentNotificationsEntityList', [
            'Email',
            'Meeting',
        ]);

        $configWriter->save();

        $em = $this->getEntityManager();

        $role = $em->createEntity('Role', [
            'name' => 'test',
            'data' => [
                'Meeting' => [
                    'read' => 'own',
                ],
                'Email' => [
                    'read' => 'own',
                ],
            ],
        ]);

        $this->user1 = $em->createEntity('User', [
            'userName' => 'test-1',
            'lastName' => 'Test 1',
            'rolesIds' => [$role->getId()],
            'emailAddress' => 'test1@test.com',
        ]);

        $this->user2 = $em->createEntity('User', [
            'userName' => 'test-2',
            'lastName' => 'Test 2',
            'rolesIds' => [$role->getId()],
            'emailAddress' => 'test2@test.com',
        ]);

        $em->createEntity('User', [
            'userName' => 'test-3',
            'lastName' => 'Test 3',
            'rolesIds' => [],
            'emailAddress' => 'test3@test.com',
        ]);

        $preferences2 = $em->getEntityById('Preferences', $this->user2->getId());

        $preferences2->set([
            'assignmentNotificationsIgnoreEntityTypeList' => ['Meeting'],
        ]);

        $em->saveEntity($preferences2);
    }

    public function testAssignmentSelf() : void
    {
        $meeting = $this->entityManager->createEntity('Meeting', [
            'name' => 'test',
            'assignedUserId' => $this->user1->getId(),
            'createdById' => $this->user1->getId(),
        ]);

        $notification = $this->entityManager
            ->getRDBRepository('Notification')
            ->where([
                'userId' => '1',
                'type' => 'Assign',
                'relatedType' => 'Meeting',
                'relatedId' => $meeting->getId(),
            ])
            ->findOne();

        $this->assertNull($notification, 'notification');
    }

    public function testAssignmentEnabled() : void
    {
        $meeting = $this->entityManager->createEntity('Meeting', [
            'name' => 'test',
            'assignedUserId' => $this->user1->getId(),
        ]);

        $notification = $this->entityManager
            ->getRDBRepository('Notification')
            ->where([
                'userId' => $this->user1->getId(),
                'type' => 'Assign',
                'relatedType' => 'Meeting',
                'relatedId' => $meeting->getId(),
            ])
            ->findOne();

        $this->assertNotNull($notification);
    }

    public function testAssignmentDisabled() : void
    {
        $call = $this->entityManager->createEntity('Call', [
            'name' => 'test',
            'assignedUserId' => $this->user1->getId(),
        ]);

        $notification = $this->entityManager
            ->getRDBRepository('Notification')
            ->where([
                'userId' => $this->user1->getId(),
                'type' => 'Assign',
                'relatedType' => 'Call',
                'relatedId' => $call->getId(),
            ])
            ->findOne();

        $this->assertNull($notification);
    }

    public function testAssignmentIgnored() : void
    {
        $meeting = $this->entityManager->createEntity('Meeting', [
            'name' => 'test',
            'assignedUserId' => $this->user2->getId(),
        ]);

        $notification = $this->entityManager
            ->getRDBRepository('Notification')
            ->where([
                'userId' => $this->user1->getId(),
                'type' => 'Assign',
                'relatedType' => 'Meeting',
                'relatedId' => $meeting->getId(),
            ])
            ->findOne();

        $this->assertNull($notification, 'notification');
    }

    public function testAssignmentEmailYes() : void
    {
        $email = $this->entityManager->createEntity('Email', [
            'name' => 'test',
            'status' => 'Archived',
            'from' => $this->user2->get('emailAddress'),
            'to' => $this->user1->get('emailAddress'),
            'dateSent' => date('Y-m-d H:i:s'),
        ]);

        $notification = $this->entityManager
            ->getRDBRepository('Notification')
            ->where([
                'userId' => $this->user1->getId(),
                'type' => 'EmailReceived',
                'relatedType' => 'Email',
                'relatedId' => $email->getId(),
            ])
            ->findOne();

        $this->assertNotNull($notification);
    }
}
