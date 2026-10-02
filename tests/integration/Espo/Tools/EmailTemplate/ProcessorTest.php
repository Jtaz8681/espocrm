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

namespace integration\Espo\Tools\EmailTemplate;

use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Entities\EmailTemplate;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Tools\EmailTemplate\Data as TemplateData;
use Espo\Tools\EmailTemplate\Params as TemplateParams;
use Espo\Tools\EmailTemplate\Processor;
use tests\integration\Core\BaseTestCase;

class ProcessorTest extends BaseTestCase
{
    public function testProcessAccess(): void
    {
        $em = $this->getEntityManager();

        $team = $em->getRDBRepositoryByClass(Team::class)->getNew();
        $team
            ->setName('Team 1');
        $em->saveEntity($team);

        $user = $em->getRDBRepositoryByClass(User::class)->getNew();
        $user
            ->setFirstName('Test')
            ->setLastName('Hello')
            ->setUserName('test')
            ->setDefaultTeam(Link::fromEntity($team))
            ->setTeams(LinkMultiple::create()->withAddedId($team->getId()));
        $em->saveEntity($user);

        $template1 = $em->getRDBRepositoryByClass(EmailTemplate::class)->getNew();
        $template1->setMultiple([
            'subject' => '{Lead.name} {Lead.assignedUser.name} {Lead.assignedUser.password}',
            'body' => '{Lead.name} {Lead.assignedUser.name} {Lead.assignedUser.password}',
        ]);
        $em->saveEntity($template1);

        $template2 = $em->getRDBRepositoryByClass(EmailTemplate::class)->getNew();
        $template2->setMultiple([
            'subject' => '{{name}} {{assignedUser.name}} {{assignedUser.password}}',
            'body' => '{{name}} {{assignedUser.name}} {{assignedUser.password}}',
        ]);
        $em->saveEntity($template2);

        $template3 = $em->getRDBRepositoryByClass(EmailTemplate::class)->getNew();
        $template3->setMultiple([
            'subject' => '{{name}} {{password}} {{defaultTeam.name}}',
            'body' => '{User.name} {User.password} {User.defaultTeam.name}',
        ]);
        $em->saveEntity($template3);

        $template4 = $em->getRDBRepositoryByClass(EmailTemplate::class)->getNew();
        $template4->setMultiple([
            'subject' => '{{name}} {{password}} {{defaultTeam.name}}',
            'body' => '{User.name} {User.password} {User.defaultTeam.name}',
        ]);
        $em->saveEntity($template4);

        $lead = $em->getRDBRepositoryByClass(Lead::class)->getNew();
        $lead
            ->setFirstName('Lead')
            ->setLastName('Abc')
            ->setAssignedUser($user);
        $em->saveEntity($lead);

        $processor = $this->getInjectableFactory()->create(Processor::class);

        $params = TemplateParams::create()
            ->withApplyAcl(false);

        $data = TemplateData::create();

        //

        $emailData1 = $processor->process($template1, $params, $data->withParent($lead));

        $this->assertEquals(
            'Lead Abc Test Hello {Lead.assignedUser.password}',
            $emailData1->getSubject(),
        );

        $this->assertEquals(
            'Lead Abc Test Hello {Lead.assignedUser.password}',
            $emailData1->getBody(),
        );

        //

        $emailData2 = $processor->process($template2, $params, $data->withParent($lead));

        $this->assertEquals(
            'Lead Abc Test Hello ',
            $emailData2->getSubject(),
        );

        $this->assertEquals(
            'Lead Abc Test Hello ',
            $emailData2->getBody(),
        );

        //

        $emailData3 = $processor->process($template3, $params, $data->withParent($user));

        $this->assertEquals(
            'Test Hello  ',
            $emailData3->getSubject(),
        );

        $this->assertEquals(
            'Test Hello {User.password} {User.defaultTeam.name}',
            $emailData3->getBody(),
        );
    }

    public function testProcessAndCleanup(): void
    {
        $em = $this->getEntityManager();

        $account = $em->getRDBRepositoryByClass(Account::class)->getNew();
        $account->setName('Account 1');
        $em->saveEntity($account);

        $lead = $em->getRDBRepositoryByClass(Lead::class)->getNew();
        $lead->setFirstName('One');
        $em->saveEntity($lead);
        $em->refreshEntity($lead);

        $template1 = $em->getRDBRepositoryByClass(EmailTemplate::class)->getNew();
        $template1->setMultiple([
            'subject' => 'Test {Person.firstName} test {Account.name}',
            'body' => 'Test {Person.name} test {Account.name}.',
        ]);
        $em->saveEntity($template1);

        //

        $processor = $this->getInjectableFactory()->create(Processor::class);

        $params = TemplateParams::create()
            ->withApplyAcl(false);

        $data = TemplateData::create();

        //

        $emailData1 = $processor->process($template1, $params, $data->withParent($account));

        $this->assertEquals(
            'Test  test Account 1',
            $emailData1->getSubject(),
        );

        $this->assertEquals(
            'Test  test Account 1.',
            $emailData1->getBody(),
        );

        //

        $emailData2 = $processor->process($template1, $params, $data->withParent($lead));

        $this->assertEquals(
            'Test One test {Account.name}',
            $emailData2->getSubject(),
        );

        $this->assertEquals(
            'Test One test {Account.name}.',
            $emailData2->getBody(),
        );
    }
}
