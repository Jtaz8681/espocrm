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

namespace tests\integration\Espo\Core\FieldProcessing;

use DateTimeImmutable;
use Espo\Core\Authentication\Util\DelayUtil;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingProcessor;
use Espo\Core\Field\DateTime;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\DateTime\Clock;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Entities\Reminder;
use tests\integration\Core\BaseTestCase;

class ReminderTest extends BaseTestCase
{
    public function testOne(): void
    {
        /* @var $entityManager EntityManager */
        $entityManager = $this->getContainer()->getByClass(EntityManager::class);

        $user = $this->getContainer()->getByClass(User::class);

        $meeting = $entityManager->createEntity('Meeting', [
            'dateStart' => DateTime::createNow()->modify('+1 day')->toString(),
            'usersIds' => [$user->getId()],
            'reminders' => [
                (object) [
                    'type' => 'Popup',
                    'seconds' => 0,
                ],
                 (object) [
                    'type' => 'Popup',
                    'seconds' => 60,
                ]
            ]
        ]);

        $reminderList = $entityManager
            ->getRDBRepository(Reminder::ENTITY_TYPE)
            ->where([
                'entityId' => $meeting->getId(),
                'entityType' => $meeting->getEntityType(),
            ])
            ->order('remindAt')
            ->find();

        $this->assertEquals(2, count($reminderList));
    }

    public function testFallsInPast(): void
    {
        $clock = $this->createMock(Clock::class);
        $clock->method('now')
            ->willReturn(new DateTimeImmutable('2030-01-01 00:00'));


        $app = $this->createApplication(
            binding: new class ($clock) implements BindingProcessor {

                public function __construct(private Clock $clock) {}

                public function process(Binder $binder): void
                {
                    $binder->bindInstance(Clock::class, $this->clock);
                }
            },
            // @todo Need to reset loaded hooks in the HookManager?
            //reuse: true,
        );
        $this->setApplication($app);

        $entityManager = $this->getEntityManager();

        $user = $this->getContainer()->getByClass(User::class);

        $meeting = $entityManager->createEntity(Meeting::ENTITY_TYPE, [
            'dateStart' => DateTime::fromDateTime($clock->now())->modify('+10 minutes')->toString(),
            'usersIds' => [$user->getId()],
            'reminders' => [
                (object) [
                    'type' => Reminder::TYPE_POPUP,
                    'seconds' => 60 * 60,
                ],
                (object) [
                    'type' => Reminder::TYPE_POPUP,
                    'seconds' => 120 * 60,
                ],
                (object) [
                    'type' => Reminder::TYPE_EMAIL,
                    'seconds' => 60 * 60,
                ],
                (object) [
                    'type' => Reminder::TYPE_EMAIL,
                    'seconds' => 120 * 60,
                ],
            ]
        ]);

        $reminderList = $entityManager
            ->getRDBRepositoryByClass(Reminder::class)
            ->where([
                'entityId' => $meeting->getId(),
                'entityType' => $meeting->getEntityType(),
            ])
            ->find();

        $this->assertCount(2, $reminderList);
    }
}
