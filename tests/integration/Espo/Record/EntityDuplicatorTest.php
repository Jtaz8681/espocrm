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

use Espo\Core\Record\CreateParams;
use Espo\Core\Record\Duplicator\EntityDuplicator;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Task;
use tests\integration\Core\BaseTestCase;

class EntityDuplicatorTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testDuplicate(): void
    {
        $user = $this->createUser([
            'userName' => 'test',
            'type' => User::TYPE_ADMIN,
        ]);

        $this->authenticate('test');

        $em = $this->getEntityManager();

        $account = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $account->setName('Account');
        $em->saveEntity($account);

        $email = $em->getRDBRepositoryByClass(Email::class)->getNew();
        $email->setSubject('Test');
        $email->setParent($account);
        $em->saveEntity($email);

        $taskService = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Task::class);

        $task = $taskService->create((object) [
            'name' => 'Task',
            'originalEmailId' => $email->getId(),
            'parentId' => $account->getId(),
            'parentType' => $account->getEntityType(),
            'assignedUserId' => $user->getId(),
        ], CreateParams::create())->getEntity();

        $this->assertEquals($email->getId(), $task->get('emailId'));

        $duplicator = $this->getInjectableFactory()->create(EntityDuplicator::class);

        $values = $duplicator->duplicate($task);

        $this->assertEquals($account->getId(), $values->accountId ?? null);
        $this->assertFalse(isset($values->emailId));
    }
}
