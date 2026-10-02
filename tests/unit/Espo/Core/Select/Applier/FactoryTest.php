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

namespace tests\unit\Espo\Core\Select\Applier;

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\AccessControl\Applier as AccessControlFilterApplier;
use Espo\Core\Select\Applier\Appliers\Additional as AdditionalApplier;
use Espo\Core\Select\Applier\Appliers\Limit as LimitApplier;
use Espo\Core\Select\Applier\Factory as ApplierFactory;
use Espo\Core\Select\Bool\Applier as BoolFilterListApplier;
use Espo\Core\Select\Order\Applier as OrderApplier;
use Espo\Core\Select\Primary\Applier as PrimaryFilterApplier;
use Espo\Core\Select\Select\Applier as SelectApplier;
use Espo\Core\Select\Text\Applier as TextFilterApplier;
use Espo\Core\Select\Where\Applier as WhereApplier;

use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\Entities\User;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    private $aclManager;
    private $acl;
    private $injectableFactory;
    private $user;
    private $factory;

    protected function setUp(): void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->user = $this->createMock(User::class);

        $userAclManagerProvider = $this->createMock(UserAclManagerProvider::class);

        $this->aclManager = $this->createMock(AclManager::class);

        $userAclManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->aclManager);

        $this->factory = new ApplierFactory(
            $this->injectableFactory,
            $userAclManagerProvider,
        );

        $this->acl = $this->createMock(Acl::class);

        $this->aclManager
            ->expects($this->any())
            ->method('createUserAcl')
            ->willReturn($this->acl);
    }

    public function testCreate1()
    {
        $this->prepareFactoryTest(SelectApplier::class, ApplierFactory::SELECT, 'createSelect');
    }

    public function testCreate2()
    {
        $this->prepareFactoryTest(
            BoolFilterListApplier::class, ApplierFactory::BOOL_FILTER_LIST, 'createBoolFilterList');
    }

    public function testCreate3()
    {
        $this->prepareFactoryTest(TextFilterApplier::class, ApplierFactory::TEXT_FILTER, 'createTextFilter');
    }

    public function testCreate4()
    {
        $this->prepareFactoryTest(WhereApplier::class, ApplierFactory::WHERE, 'createWhere');
    }

    public function testCreate5()
    {
        $this->prepareFactoryTest(OrderApplier::class, ApplierFactory::ORDER, 'createOrder');
    }

    public function testCreate6()
    {
        $this->prepareFactoryTest(LimitApplier::class, ApplierFactory::LIMIT, 'createLimit');
    }

    public function testCreate7()
    {
        $this->prepareFactoryTest(AdditionalApplier::class, ApplierFactory::ADDITIONAL, 'createAdditional');
    }

    public function testCreate8()
    {
        $this->prepareFactoryTest(
            PrimaryFilterApplier::class, ApplierFactory::PRIMARY_FILTER, 'createPrimaryFilter');
    }

    public function testCreate9()
    {
        $this->prepareFactoryTest(
            AccessControlFilterApplier::class, ApplierFactory::ACCESS_CONTROL_FILTER, 'createAccessControlFilter');
    }

    protected function prepareFactoryTest(string $defaultClassName, string $type, string $method)
    {
        $entityType = 'Test';

        $applierClassName = $className ?? $defaultClassName;

        $applier = $this->createMock($defaultClassName);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $this->user)
            ->bindInstance(AclManager::class, $this->aclManager)
            ->bindInstance(Acl::class, $this->acl)
            ->for($applierClassName)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWithBinding')
            ->with($applierClassName, $bindingContainer)
            ->willReturn($applier);

        $resultApplier = $this->factory->$method(
            $entityType,
            $this->user,
            $type
        );

        $this->assertEquals($applier, $resultApplier);
    }
}
