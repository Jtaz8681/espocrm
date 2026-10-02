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

namespace tests\unit\Espo\Core\Select\Where;

use Espo\Core\Utils\Acl\UserAclManagerProvider;

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\Where\Checker;
use Espo\Core\Select\Where\CheckerFactory;
use Espo\Entities\User;
use PHPUnit\Framework\TestCase;

class CheckerFactoryTest extends TestCase
{
    private $injectableFactory;
    private $user;
    private $acl;
    private $factory;

    protected function setUp(): void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $aclManager = $this->createMock(AclManager::class);
        $this->user = $this->createMock(User::class);
        $this->acl = $this->createMock(Acl::class);
        $userAclFilterResolver = $this->createMock(UserAclManagerProvider::class);

        $aclManager
            ->expects($this->any())
            ->method('createUserAcl')
            ->with($this->user)
            ->willReturn($this->acl);

        $userAclFilterResolver
            ->expects($this->any())
            ->method('get')
            ->with($this->user)
            ->willReturn($aclManager);

        $this->factory = new CheckerFactory(
            $this->injectableFactory,
            $userAclFilterResolver
        );
    }

    public function testCreate1()
    {
        $this->prepareFactoryTest();
    }

    protected function prepareFactoryTest()
    {
        $entityType = 'Test';

        $object = $this->createMock(Checker::class);

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWith')
            ->with(
                Checker::class,
                [
                    'entityType' => $entityType,
                    'acl' => $this->acl,
                ]
            )
            ->willReturn($object);

        $resultObject = $this->factory->create(
            $entityType,
            $this->user
        );

        $this->assertEquals($object, $resultObject);
    }
}
