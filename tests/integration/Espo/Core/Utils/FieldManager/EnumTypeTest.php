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

namespace tests\integration\Espo\Core\Utils\FieldManager;

use Espo\Core\Utils\Metadata;
use Espo\ORM\EntityManager;
use tests\integration\Core\BaseTestCase;

class EnumTypeTest extends BaseTestCase
{
    private $jsonFieldDefs = '{
        "type":"enum",
        "required":true,
        "dynamicLogicVisible":null,
        "dynamicLogicRequired":null,
        "dynamicLogicReadOnly":null,
        "dynamicLogicOptions":null,
        "name":"testEnum",
        "label":"TestEnum",
        "audited":true,
        "options":["option1","option2","option3"],
        "translatedOptions":{"option1":"option1","option2":"option2","option3":"option3"},
        "default":"option2",
        "tooltipText":"",
        "isPersonalData":false,
        "isSorted":false,
        "readOnly":false,
        "tooltip":false
    }';

    protected function createFieldManager($app = null)
    {
        if (!$app) {
            $app = $this;
        }

        return $app->getContainer()->get('injectableFactory')->create(
            'Espo\\Tools\\FieldManager\\FieldManager'
        );
    }

    public function testCreate()
    {
        $fieldManager = $this->createFieldManager();

        $fieldDefs = get_object_vars(json_decode($this->jsonFieldDefs));

        $fieldManager->create('Account', 'testEnum', $fieldDefs);
        $this->getDataManager()->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->getByClass(Metadata::class);
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestEnum');

        $this->assertEquals('enum', $savedFieldDefs['type']);
        $this->assertEquals('option2', $savedFieldDefs['default']);
        $this->assertTrue($savedFieldDefs['required']);
        $this->assertTrue($savedFieldDefs['isCustom']);
        $this->assertTrue($savedFieldDefs['audited']);

        $entityManager = $app->getContainer()->getByClass(EntityManager::class);

        $account = $entityManager->getNewEntity('Account');
        $account->set([
            'name' => 'Test',
            'cTestEnum' => 'option1',
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntityById('Account', $account->getId());
        $this->assertEquals('option1', $account->get('cTestEnum'));
    }

    public function testUpdate()
    {
        $this->testCreate();

        $app = $this->createApplication();

        $fieldManager = $this->createFieldManager($app);

        $fieldDefs = get_object_vars(json_decode($this->jsonFieldDefs));
        $fieldDefs['required'] = false;
        $fieldDefs['default'] = 'option3';
        $fieldDefs['readOnly'] = true;

        $fieldManager->update('Account', 'cTestEnum', $fieldDefs);
        $this->getDataManager()->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestEnum');

        $this->assertFalse($savedFieldDefs['required']);
        $this->assertEquals('option3', $savedFieldDefs['default']);
        $this->assertTrue($savedFieldDefs['audited']);
        $this->assertTrue($savedFieldDefs['readOnly']);

        $entityManager = $app->getContainer()->getByClass(EntityManager::class);

        $account = $entityManager->getNewEntity('Account');
        $account->set([
            'name' => 'New Test',
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntityById('Account', $account->getId());
        $this->assertEquals('option3', $account->get('cTestEnum'));
    }
}
