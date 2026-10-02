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

namespace integration\Espo\Core\Authentication\Oidc;

use Espo\Core\Authentication\Oidc\UserProvider\DefaultUserInfoPopulator;
use Espo\Core\Authentication\Oidc\UserProvider\UserInfo;
use Espo\Core\Field\EmailAddress;
use Espo\Core\Field\EmailAddressGroup;
use Espo\Core\Field\PhoneNumber;
use Espo\Core\Field\PhoneNumberGroup;
use Espo\Entities\User;
use tests\integration\Core\BaseTestCase;

class UserInfoPopulatorTest extends BaseTestCase
{
    public function testPopulate(): void
    {
        $em = $this->getEntityManager();

        $user = $em->getRDBRepositoryByClass(User::class)->getNew();

        $user
            ->setUserName('name')
            ->setPhoneNumberGroup(
                PhoneNumberGroup::create([
                    PhoneNumber::create('+100'),
                    PhoneNumber::create('+200'),
                ])
            )
            ->setEmailAddressGroup(
                EmailAddressGroup::create([
                    EmailAddress::create('test1@test.com'),
                    EmailAddress::create('test2@test.com'),
                ]),
            );

        $em->saveEntity($user);

        $userInfo = $this->createMock(UserInfo::class);
        $userInfo
            ->method('get')
            ->willReturnMap([
                ['email', 'test3@test.com'],
                ['phone_number', '+300'],
            ]);

        $populator = $this->getInjectableFactory()->create(DefaultUserInfoPopulator::class);

        //

        $populator->populate($userInfo, $user);

        $em->saveEntity($user);
        $em->refreshEntity($user);

        $this->assertCount(2, $user->getEmailAddressGroup()->getList());
        $this->assertCount(2, $user->getPhoneNumberGroup()->getList());

        $this->assertEquals('test3@test.com', $user->getEmailAddress());
        $this->assertEquals('+300', $user->getPhoneNumber());

        $this->assertEquals('test2@test.com', $user->getEmailAddressGroup()->getList()[1]->getAddress());
        $this->assertEquals('+200', $user->getPhoneNumberGroup()->getList()[1]->getNumber());

        //

        $userInfo = $this->createMock(UserInfo::class);
        $userInfo
            ->method('get')
            ->willReturnMap([
                ['email', null],
                ['phone_number', null],
            ]);

        $populator->populate($userInfo, $user);

        $em->saveEntity($user);
        $em->refreshEntity($user);

        $this->assertEquals('test2@test.com', $user->getEmailAddress());
        $this->assertEquals('+200', $user->getPhoneNumber());

        $this->assertCount(1, $user->getEmailAddressGroup()->getList());
        $this->assertCount(1, $user->getPhoneNumberGroup()->getList());
    }
}
