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

namespace tests\integration\Espo\Core\Acl;

use Espo\Core\AclManager;
use Espo\Core\Acl;
use Espo\Core\Field\LinkMultiple;
use Espo\Entities\Role;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Entities\Task;
use tests\integration\Core\BaseTestCase;

class AclTest extends BaseTestCase
{
    public function testGetReadOwnerUserField()
    {
        $aclManager = $this->getContainer()->getByClass(AclManager::class);

        $this->assertEquals(
            'assignedUser',
            $aclManager->getReadOwnerUserField('Account')
        );

        $this->assertEquals(
            'createdBy',
            $aclManager->getReadOwnerUserField('Import')
        );

        $this->assertEquals(
            'users',
            $aclManager->getReadOwnerUserField('Email')
        );

        $this->assertEquals(
            'users',
            $aclManager->getReadOwnerUserField('Meeting')
        );
    }

    public function testTryCheck(): void
    {
        /* @var $Acl Acl */
        $acl = $this->getContainer()->get('acl');

        $this->assertFalse($acl->tryCheck('NonExistingEntityType'));
    }

    public function testCheckScopeAccess(): void
    {
        $this->createUser('tester', [
            'data' => [
                Task::ENTITY_TYPE => false,
                Call::ENTITY_TYPE => [
                    'create' => Acl\Table::LEVEL_NO,
                    'read' => Acl\Table::LEVEL_NO,
                    'edit' => Acl\Table::LEVEL_NO,
                    'delete' => Acl\Table::LEVEL_NO,
                ],
                Meeting::ENTITY_TYPE => [
                    'create' => Acl\Table::LEVEL_YES,
                    'read' => Acl\Table::LEVEL_YES,
                    'edit' => Acl\Table::LEVEL_NO,
                    'delete' => Acl\Table::LEVEL_NO,
                ],
            ],
        ]);

        $this->authenticate('tester');

        $acl = $this->getContainer()->getByClass(Acl::class);

        $this->assertFalse($acl->checkScope(Task::ENTITY_TYPE));
        $this->assertTrue($acl->checkScope(Call::ENTITY_TYPE));
        $this->assertTrue($acl->checkScope(Meeting::ENTITY_TYPE));

        $this->assertFalse($acl->check(Task::ENTITY_TYPE, Acl\Table::ACTION_READ));
        $this->assertFalse($acl->check(Call::ENTITY_TYPE, Acl\Table::ACTION_READ));
        $this->assertTrue($acl->check(Meeting::ENTITY_TYPE, Acl\Table::ACTION_READ));
    }

    public function testCheckField(): void
    {
        $user = $this->createUser('tester', [
            'fieldData' => [
                'Call' => [
                    'direction' => [
                        'read' => Acl\Table::LEVEL_NO,
                        'edit' => Acl\Table::LEVEL_NO,
                    ],
                    'contacts' => [
                        'read' => Acl\Table::LEVEL_YES,
                        'edit' => Acl\Table::LEVEL_NO,
                    ],
                ],
            ],
        ]);

        /** @var User $user */
        $user = $this->getEntityManager()->getEntityById(User::ENTITY_TYPE, $user->getId());

        $aclManager = $this->getContainer()->getByClass(AclManager::class);

        $this->assertFalse($aclManager->checkField($user, 'Call', 'direction'));
        $this->assertTrue($aclManager->checkField($user, 'Call', 'contacts'));
        $this->assertFalse($aclManager->checkField($user, 'Call', 'contacts', Acl\Table::ACTION_EDIT));
    }

    public function testDisabledField(): void
    {
        $metadata = $this->getMetadata();

        $metadata->set('entityDefs', 'Account', [
            'fields' => [
                'assignedUser' => [
                    'disabled' => true,
                ]
            ]
        ]);
        $metadata->save();

        $this->reCreateApplication(reuse: true);

        $acl = $this->getContainer()->getByClass(Acl::class);

        $this->assertFalse($acl->checkField(Account::ENTITY_TYPE, 'assignedUser'));
        $this->assertTrue($acl->checkField(Account::ENTITY_TYPE, 'name'));
    }

    public function testDisabledLink(): void
    {
        $metadata = $this->getMetadata();

        $metadata->set('entityDefs', 'Account', [
            'links' => [
                'opportunities' => [
                    'disabled' => true,
                ]
            ]
        ]);
        $metadata->save();

        $this->reCreateApplication(reuse: true);

        $acl = $this->getContainer()->getByClass(Acl::class);

        $this->assertFalse($acl->checkLink('Account', 'opportunities'));
    }

    public function testMultipleRoles(): void
    {
        $em = $this->getEntityManager();

        $role1 = $em->getRDBRepositoryByClass(Role::class)->getNew();
        $role1->setRawData([
            'Lead' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_YES,
            ],
            'Opportunity' => [
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_NO,
            ],
            'Account' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_NO,
            ],
            'Meeting' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_YES,
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_NO,
                Acl\Table::ACTION_STREAM => Acl\Table::LEVEL_NO,
            ],
            'Call' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_NO,
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_ALL,
            ],
        ]);
        $em->saveEntity($role1);

        $role2 = $em->getRDBRepositoryByClass(Role::class)->getNew();
        $role2->setRawData([
            'Lead' => [
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_NO,
            ],
            'Opportunity' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_YES,
            ],
            'Account' => [
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_NO,
            ],
            'Meeting' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_NO,
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_ALL,
                Acl\Table::ACTION_STREAM => Acl\Table::LEVEL_NO,
            ],
            'Call' => [
                Acl\Table::ACTION_CREATE => Acl\Table::LEVEL_YES,
                Acl\Table::ACTION_EDIT => Acl\Table::LEVEL_NO,
            ],
        ]);
        $em->saveEntity($role2);

        $user = $em->getRDBRepositoryByClass(User::class)->getNew();
        $user
            ->setUserName('test-1')
            ->setRoles(LinkMultiple::create()->withAddedIdList([$role1->getId(), $role2->getId()]));
        $em->saveEntity($user);

        $em->refreshEntity($user);

        $aclManager = $this->getContainer()->getByClass(AclManager::class);

        $this->assertTrue($aclManager->checkScope($user, 'Lead', Acl\Table::ACTION_CREATE));
        $this->assertTrue($aclManager->checkScope($user, 'Opportunity', Acl\Table::ACTION_CREATE));
        $this->assertFalse($aclManager->checkScope($user, 'Account', Acl\Table::ACTION_CREATE));
        $this->assertFalse($aclManager->checkScope($user, 'Opportunity', Acl\Table::ACTION_EDIT));
        $this->assertFalse($aclManager->checkScope($user, 'Account', Acl\Table::ACTION_EDIT));
        $this->assertTrue($aclManager->checkScope($user, 'Meeting', Acl\Table::ACTION_CREATE));
        $this->assertTrue($aclManager->checkScope($user, 'Meeting', Acl\Table::ACTION_EDIT));
        $this->assertFalse($aclManager->checkScope($user, 'Meeting', Acl\Table::ACTION_STREAM));
        $this->assertTrue($aclManager->checkScope($user, 'Call', Acl\Table::ACTION_CREATE));
        $this->assertTrue($aclManager->checkScope($user, 'Call', Acl\Table::ACTION_EDIT));
    }
}
