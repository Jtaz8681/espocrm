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

namespace tests\integration\Espo\Tools\DynamicLogic;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Tools\EntityManager\EntityManager;
use Espo\Tools\FieldManager\FieldManager;
use Espo\Tools\LinkManager\LinkManager;
use tests\integration\Core\BaseTestCase;

class CascadingFieldsTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testLinkMultiple(): void
    {
        $emTool = $this->getInjectableFactory()->create(EntityManager::class);
        $lmTool = $this->getInjectableFactory()->create(LinkManager::class);
        $fmTool = $this->getInjectableFactory()->create(FieldManager::class);

        $emTool->create('MyTest', 'Base');

        $lmTool->create([
            'link' => 'accounts',
            'linkForeign' => 'tests',
            'linkType' => 'manyToMany',
            'entity' => 'CMyTest',
            'entityForeign' => Account::ENTITY_TYPE,
            'label' => 'Account',
            'labelForeign' => 'Test',
            'relationName' => 'testAccount',
            'linkMultipleField' => true,
            'linkMultipleFieldForeign' => true,
        ]);

        $lmTool->create([
            'link' => 'contacts',
            'linkForeign' => 'tests',
            'linkType' => 'manyToMany',
            'entity' => 'CMyTest',
            'entityForeign' => Contact::ENTITY_TYPE,
            'label' => 'Contact',
            'labelForeign' => 'Test',
            'relationName' => 'testContact',
            'linkMultipleField' => true,
            'linkMultipleFieldForeign' => true,
        ]);

        $fmTool->update('CMyTest', 'contacts', [
            'dynamicLogicCascading' => [
                'items' => [
                    [
                        'localField' => 'accounts',
                        'foreignField' => 'accounts',
                        'matchRequired' => true,
                    ]
                ]
            ]
        ]);

        $this->reCreateApplication();

        $em = $this->getEntityManager();

        $a1 = $em->createEntity(Account::ENTITY_TYPE);
        $a2 = $em->createEntity(Account::ENTITY_TYPE);

        $c1A12 = $em->createEntity(Contact::ENTITY_TYPE, [
            'accountsIds' => [$a1->getId(), $a2->getId()],
            'accountId' => $a1->getId(),
        ]);

        $c2A1 = $em->createEntity(Contact::ENTITY_TYPE, [
            'accountsIds' => [$a1->getId()],
            'accountId' => $a1->getId(),
        ]);

        $service = $this->getContainer()->getByClass(ServiceContainer::class)->get('CMyTest');

        //

        $service->create((object) [
            'name' => 't1',
            'accountsIds' => [$a1->getId()],
            'contactsIds' => [$c2A1->getId()],
        ], CreateParams::create());

        //

        $service->create((object) [
            'name' => 't1',
            'accountsIds' => [$a1->getId(), $a2->getId()],
            'contactsIds' => [$c1A12->getId()],
        ], CreateParams::create());

        //

        $thrown = false;

        try {
            $service->create((object) [
                'name' => 't1',
                'accountsIds' => [$a1->getId(), $a2->getId()],
                'contactsIds' => [$c2A1->getId()],
            ], CreateParams::create());
        } catch (BadRequest) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testLink(): void
    {
        $emTool = $this->getInjectableFactory()->create(EntityManager::class);
        $lmTool = $this->getInjectableFactory()->create(LinkManager::class);
        $fmTool = $this->getInjectableFactory()->create(FieldManager::class);

        $emTool->create('MyTest', 'Base');

        $lmTool->create([
            'link' => 'account',
            'linkForeign' => 'tests',
            'linkType' => 'manyToOne',
            'entity' => 'CMyTest',
            'entityForeign' => Account::ENTITY_TYPE,
            'label' => 'Account',
            'labelForeign' => 'Test',
            'linkMultipleField' => true,
            'linkMultipleFieldForeign' => true,
        ]);

        $lmTool->create([
            'link' => 'contact',
            'linkForeign' => 'tests',
            'linkType' => 'manyToOne',
            'entity' => 'CMyTest',
            'entityForeign' => Contact::ENTITY_TYPE,
            'label' => 'Contact',
            'labelForeign' => 'Test',
            'linkMultipleField' => true,
            'linkMultipleFieldForeign' => true,
        ]);

        $fmTool->update('CMyTest', 'contact', [
            'dynamicLogicCascading' => [
                'items' => [
                    [
                        'localField' => 'account',
                        'foreignField' => 'accounts',
                        'matchRequired' => true,
                    ]
                ]
            ]
        ]);

        $this->reCreateApplication();

        $em = $this->getEntityManager();

        $a1 = $em->createEntity(Account::ENTITY_TYPE);
        $a2 = $em->createEntity(Account::ENTITY_TYPE);

        $c1A12 = $em->createEntity(Contact::ENTITY_TYPE, [
            'accountsIds' => [$a1->getId(), $a2->getId()],
            'accountId' => $a1->getId(),
        ]);

        $c2A1 = $em->createEntity(Contact::ENTITY_TYPE, [
            'accountsIds' => [$a1->getId()],
            'accountId' => $a1->getId(),
        ]);

        $service = $this->getContainer()->getByClass(ServiceContainer::class)->get('CMyTest');

        //

        $service->create((object) [
            'name' => 't1',
            'accountId' => $a1->getId(),
            'contactId' => $c2A1->getId(),
        ], CreateParams::create());

        //

        $service->create((object) [
            'name' => 't1',
            'accountId' => $a2->getId(),
            'contactId' => $c1A12->getId(),
        ], CreateParams::create());

        //

        $thrown = false;

        try {
            $service->create((object) [
                'name' => 't1',
                'accountId' => $a2->getId(),
                'contactId' => $c2A1->getId(),
            ], CreateParams::create());
        } catch (BadRequest) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }
}
