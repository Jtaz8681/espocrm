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

use tests\integration\Core\BaseTestCase;

class VarcharTypeTest extends BaseTestCase
{
    private $jsonFieldDefs = '{
        "type":"varchar",
        "required":true,
        "trim":true,
        "dynamicLogicVisible":null,
        "dynamicLogicRequired":null,
        "dynamicLogicReadOnly":null,
        "name":"testVarchar",
        "label":"TestVarchar",
        "tooltipText":"",
        "isPersonalData":false,
        "default":"",
        "maxLength":null,
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

        $fieldManager->create('Account', 'testVarchar', $fieldDefs);
        $this->getContainer()->get('dataManager')->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestVarchar');

        $this->assertArrayHasKey('type', $savedFieldDefs);
        $this->assertArrayHasKey('isCustom', $savedFieldDefs);
        $this->assertEquals('varchar', $savedFieldDefs['type']);
        $this->assertTrue($savedFieldDefs['required']);
        $this->assertTrue($savedFieldDefs['isCustom']);

        $entityManager = $app->getContainer()->get('entityManager');
        $account = $entityManager->getEntity('Account');
        $account->set([
            'name' => 'Test',
            'cTestVarchar' => 'test-value'
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntity('Account', $account->getId());
        $this->assertEquals('test-value', $account->get('cTestVarchar'));
    }

    public function testUpdate()
    {
        $this->testCreate();

        $app = $this->createApplication();

        $fieldManager = $this->createFieldManager($app);

        $fieldDefs = get_object_vars(json_decode($this->jsonFieldDefs));
        $fieldDefs['required'] = false;
        $fieldDefs['default'] = 'default-value';

        $fieldManager->update('Account', 'cTestVarchar', $fieldDefs);
        $this->getContainer()->get('dataManager')->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestVarchar');

        $this->assertFalse($savedFieldDefs['required']);
        $this->assertEquals('default-value', $savedFieldDefs['default']);

        $entityManager = $app->getContainer()->get('entityManager');

        $account = $entityManager->getEntity('Account');
        $account->set([
            'name' => 'New Test',
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntity('Account', $account->getId());
        $this->assertEquals('default-value', $account->get('cTestVarchar'));
    }
}
