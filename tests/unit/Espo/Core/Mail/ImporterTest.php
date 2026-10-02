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

namespace tests\unit\Espo\Core\Mail;

use Espo\Entities\Email;

use Espo\Core\Notification\AssignmentNotificator;

use Espo\ORM\Metadata;
use Espo\ORM\Value\ValueAccessor;
use Espo\ORM\Value\ValueAccessorFactory;
use Espo\Core\FieldProcessing\Relation\LinkMultipleSaver;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Core\Mail\Importer;
use Espo\Core\Mail\Importer\Data as ImporterData;
use Espo\Core\Mail\MessageWrapper;
use Espo\Core\Mail\ParserFactory;
use Espo\Core\Mail\Parsers\MailMimeParser;
use Espo\Core\Notification\AssignmentNotificatorFactory;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Repositories\Database;
use Espo\Core\Utils\Config;
use Espo\ORM\Repository\RDBSelectBuilder;

use PHPUnit\Framework\TestCase;

class ImporterTest extends TestCase
{
    private $emailRepository;
    private $config;
    private $assignmentNotificatorFactory;
    private $parserFactory;
    private $linkMultipleSaver;
    private $parentFinder;
    private $jobSchedulerFactory;
    private $duplicateFinder;
    private $email;
    private $repositoryMap;
    private $entityManager;

    protected function setUp(): void
    {
        $entityManager = $this->entityManager = $this->createMock(EntityManager::class);

        $this->config = $this->createMock(Config::class);

        $emailRepository = $this->createMock(Database::class);
        $emptyRepository = $this->createMock(Database::class);

        $metadata = $this->createMock(Metadata::class);

        $selectBuilder = $this->createMock(RDBSelectBuilder::class);

        $this->assignmentNotificatorFactory = $this->createMock(AssignmentNotificatorFactory::class);
        $this->parserFactory = $this->createMock(ParserFactory::class);
        $this->linkMultipleSaver = $this->createMock(LinkMultipleSaver::class);

        $this->parentFinder = $this->createMock(Importer\ParentFinder::class);

        $this->parserFactory
            ->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new MailMimeParser($this->entityManager);
                }
            );

        $emailRepository
            ->expects($this->any())
            ->method('where')
            ->willReturn($selectBuilder);

        $emailRepository
            ->expects($this->any())
            ->method('select')
            ->willReturn($selectBuilder);

        $entityManager
            ->expects($this->any())
            ->method('getMetadata')
            ->willReturn($metadata);

        $metadata
            ->expects($this->any())
            ->method('get')
            ->willReturn(null);

        $emptyRepository
            ->expects($this->any())
            ->method('where')
            ->willReturn($selectBuilder);

        $this->emailRepository = $emailRepository;

        $this->repositoryMap = [
             [Email::class, $this->emailRepository],
             //['Account', $emptyRepository],
             //['Contact', $emptyRepository],
             //['Lead', $emptyRepository],
        ];

        $valueAccessor = $this->createMock(ValueAccessor::class);
        $valueAccessorFactory = $this->createMock(ValueAccessorFactory::class);

        $valueAccessorFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn(
                $valueAccessor
            );

        $emailDefs = require('tests/unit/testData/Core/Mail/email_defs.php');
        //$attachmentDefs = require('tests/unit/testData/Core/Mail/attachment_defs.php');

        $this->email = new Email('Email', $emailDefs, $entityManager, $valueAccessorFactory);
        //$attachment = new Attachment('Attachment', $attachmentDefs, $entityManager, $valueAccessorFactory);

        $this->assignmentNotificatorFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn(
                $this->createMock(AssignmentNotificator::class)
            );


        $this->duplicateFinder = $this->createMock(Importer\DuplicateFinder::class);

        $this->jobSchedulerFactory = $this->createMock(JobSchedulerFactory::class);
    }

    public function testImport1(): void
    {
        $entityManager = $this->entityManager;
        $config = $this->config;
        $email = $this->email;

        $entityManager
            ->expects($this->any())
            ->method('getRDBRepositoryByClass')
            ->willReturnMap($this->repositoryMap);

        $entityManager
            ->expects($this->exactly(2))
            ->method('saveEntity')
            ->with($this->isInstanceOf(Email::class))
            ->willReturnCallback(function (Email $entity) {
                $entity->set('id', 'test-id');
            });

        $this->emailRepository
            ->expects($this->once())
            ->method('getNew')
            ->willReturn($email);

        $config
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['b2cMode', false]
                ]
            );

        $contents = file_get_contents('tests/unit/testData/Core/Mail/test_email_1.eml');

        $importer = new Importer\DefaultImporter(
            entityManager: $entityManager,
            config: $config,
            notificatorFactory: $this->assignmentNotificatorFactory,
            parserFactory: $this->parserFactory,
            linkMultipleSaver: $this->linkMultipleSaver,
            duplicateFinder: $this->duplicateFinder,
            jobSchedulerFactory: $this->jobSchedulerFactory,
            parentFinder: $this->parentFinder,
            autoReplyDetector: $this->createMock(Importer\AutoReplyDetector::class),
        );

        $message = new MessageWrapper(0, null, null, $contents);

        $data = ImporterData
            ::create()
            ->withTeamIdList(['teamTestId'])
            ->withUserIdList(['userTestId']);

        $email = $importer->import($message, $data);

        $this->assertEquals('test 3', $email->get('name'));

        $userIdList = $email->getLinkMultipleIdList('users');
        $this->assertTrue(in_array('userTestId', $userIdList));

        $this->assertStringContainsString('<br>Admin Test', $email->get('body'));
        $this->assertStringContainsString('Admin Test', $email->get('bodyPlain'));

        $messageId = '<e558c4dfc2a0f0d60f5ebff474c97ffc/1466410740/1950@espo>';

        $this->assertEquals($messageId, $email->get('messageId'));
        $this->assertEquals($messageId . '-r@test.com', $email->get('messageIdInternal'));
    }
}
