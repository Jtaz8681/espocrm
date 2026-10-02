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

use Espo\ORM\EntityManager;
use tests\integration\Core\BaseTestCase;

class ArrayTypeTest extends BaseTestCase
{
    private $jsonFieldDefs = '{
        "name":"testArray",
        "label":"TestArray",
        "type":"array",
        "required":false,
        "noEmptyString":false,
        "dynamicLogicVisible":null,
        "dynamicLogicRequired":null,
        "dynamicLogicReadOnly":null,
        "dynamicLogicOptions":null,
        "options":["option1","option2","option3"],
        "translatedOptions":{"option1":"option1","option2":"option2","option3":"option3"},
        "tooltipText":null,
        "isPersonalData":false,
        "inlineEditDisabled":false,
        "audited":false,
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

        $fieldManager->create('Account', 'testArray', $fieldDefs);

        $this->getContainer()->get('dataManager')->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestArray');

        $this->assertArrayHasKey('type', $savedFieldDefs);
        $this->assertArrayHasKey('isCustom', $savedFieldDefs);
        $this->assertArrayHasKey('options', $savedFieldDefs);
        $this->assertEquals('array', $savedFieldDefs['type']);
        $this->assertTrue($savedFieldDefs['isCustom']);

        $entityManager = $app->getContainer()->getByClass(EntityManager::class);

        $account = $entityManager->getNewEntity('Account');
        $account->set([
            'name' => 'Test',
            'cTestArray' => ['option1', 'option3']
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntityById('Account', $account->getId());
        $this->assertEquals(['option1', 'option3'], $account->get('cTestArray'));
    }

    public function testUpdate()
    {
        $this->testCreate();

        $app = $this->createApplication();

        $fieldManager = $this->createFieldManager($app);

        $fieldDefs = get_object_vars(json_decode($this->jsonFieldDefs));
        $fieldDefs['required'] = true;

        $fieldManager->update('Account', 'cTestArray', $fieldDefs);

        $this->getContainer()->get('dataManager')->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestArray');

        $this->assertTrue($savedFieldDefs['required']);

        $entityManager = $app->getContainer()->getByClass(EntityManager::class);
        $account = $entityManager->getNewEntity('Account');
        $account->set([
            'name' => 'Test',
        ]);

        $entityManager->saveEntity($account);
    }
}
