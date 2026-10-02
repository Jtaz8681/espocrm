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

namespace tests\integration\Espo\Export;

use Espo\ORM\EntityManager;

use Espo\Core\{
    FileStorage\Manager as FileStorageManager,
    Select\SearchParams,
    Select\Where\Item as WhereItem,
};

use Espo\Tools\Export\{
    Factory,
    Params,
};

class ExportTest extends \tests\integration\Core\BaseTestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var FileStorageManager
     */
    private $fileStorageManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()->get('entityManager');

        $this->factory = $this->getContainer()
            ->get('injectableFactory')
            ->create(Factory::class);

        $this->fileStorageManager = $this->getContainer()->get('fileStorageManager');
    }

    public function testCsvWithFieldList(): void
    {
        $user =$this->entityManager->createEntity('User', [
            'id' => 'user-id',
            'userName' => 'user',
            'lastName' => 'User',
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '1',
            'name' => 'test-1',
            'assignedUserId' => $user->getId(),
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '2',
            'name' => 'test-2',
            'assignedUserId' => $user->getId(),
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '3',
            'name' => 'test-3',
        ]);

        $searchParams = SearchParams
            ::create()
            ->withWhere(WhereItem::fromRaw([
                'type' => 'equals',
                'attribute' => 'assignedUserId',
                'value' => $user->getId(),
            ]));

        $params = Params
            ::create('Task')
            ->withFieldList([
                'name',
                'assignedUser',
            ])
            ->withSearchParams($searchParams)
            ->withFormat('csv');

        $export = $this->factory->create();

        $attachmentId = $export
            ->setParams($params)
            ->run()
            ->getAttachmentId();

        $attachment = $this->entityManager->getEntityById('Attachment', $attachmentId);

        $contents = $this->fileStorageManager->getContents($attachment);

        $expectedContents =
            "name,assignedUserId,assignedUserName\n" .
            "test-2,user-id,User\n" .
            "test-1,user-id,User\n";

        $this->assertEquals($expectedContents, $contents);
    }

    public function testCsvWithAttributeList(): void
    {
        $user = $this->entityManager->createEntity('User', [
            'id' => 'user-id',
            'userName' => 'user',
            'lastName' => 'User',
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '1',
            'name' => 'test-1',
            'assignedUserId' => $user->getId(),
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '2',
            'name' => 'test-2',
            'assignedUserId' => $user->getId(),
        ]);

        $params = Params
            ::create('Task')
            ->withAttributeList([
                'id',
                'name',
                'assignedUserId',
            ])
            ->withAccessControl(false)
            ->withFormat('csv');

        $export = $this->factory->create();

        $attachmentId = $export
            ->setParams($params)
            ->run()
            ->getAttachmentId();

        $attachment = $this->entityManager->getEntityById('Attachment', $attachmentId);

        $contents = $this->fileStorageManager->getContents($attachment);

        $expectedContents =
            "id,name,assignedUserId\n" .
            "2,test-2,user-id\n" .
            "1,test-1,user-id\n";

        $this->assertEquals($expectedContents, $contents);
    }

    public function testCsvCollection(): void
    {
        $user = $this->entityManager->createEntity('User', [
            'id' => 'user-id',
            'userName' => 'user',
            'lastName' => 'User',
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '1',
            'name' => 'test-1',
            'assignedUserId' => $user->getId(),
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '2',
            'name' => 'test-2',
            'assignedUserId' => $user->getId(),
        ]);

        $this->entityManager->createEntity('Task', [
            'id' => '3',
            'name' => 'test-3',
        ]);

        $collection = $this->entityManager
            ->getRDBRepository('Task')
            ->where([
                'assignedUserId' => $user->getId(),
            ])
            ->order('id', 'ASC')
            ->find();

        $params = Params
            ::create('Task')
            ->withAttributeList([
                'name',
                'assignedUserId',
            ])
            ->withFieldList([
                'name',
                'assignedUser',
            ])
            ->withFormat('csv');

        $export = $this->factory->create();

        $attachmentId = $export
            ->setParams($params)
            ->setCollection($collection)
            ->run()
            ->getAttachmentId();

        $attachment = $this->entityManager->getEntityById('Attachment', $attachmentId);

        $contents = $this->fileStorageManager->getContents($attachment);

        $expectedContents =
            "name,assignedUserId\n" .
            "test-1,user-id\n" .
            "test-2,user-id\n";

        $this->assertEquals($expectedContents, $contents);
    }
}
