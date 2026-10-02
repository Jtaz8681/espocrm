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

namespace tests\integration\Espo\Core\Job;

use Espo\Core\InjectableFactory;
use Espo\Core\Job\Job\Status;
use Espo\Core\Job\JobManager;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Core\Job\QueueName;

use Espo\Entities\Job as JobEntity;
use Espo\ORM\EntityManager;
use tests\integration\Core\BaseTestCase;
use tests\integration\testClasses\Job\Job as TestJob;

class JobTest extends BaseTestCase
{
    /**
     * @var JobManager
     */
    private $jobManager;


    /** @var EntityManager */
    private $entityManager;

    /**
     * @var JobSchedulerFactory
     */
    private $schedulerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobManager = $this->getContainer()->get('jobManager');

        $this->entityManager = $this->getContainer()->getByClass(EntityManager::class);

        $this->schedulerFactory = $this->getContainer()
            ->getByClass(InjectableFactory::class)
            ->create(JobSchedulerFactory::class);
    }

    public function testScheduler(): void
    {
        $this->schedulerFactory
            ->create()
            ->setClassName(TestJob::class)
            ->setQueue(QueueName::Q0)
            ->schedule();

        $this->jobManager->processQueue(QueueName::Q0, 10);

        $job = $this->entityManager
            ->getRDBRepositoryByClass(JobEntity::class)
            ->where(['className' => TestJob::class])
            ->findOne();

        $this->assertNotNull($job);

        $this->assertEquals(Status::SUCCESS, $job->getStatus());
    }

    public function testProcessQueueNoGroup(): void
    {
        $job = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'queue' => 'q0',
        ]);

        $this->jobManager->processQueue('q0', 10);

        $jobReloaded = $this->entityManager->getEntityById('Job', $job->getId());

        $this->assertEquals(Status::SUCCESS, $jobReloaded->getStatus());
    }

    public function testProcessQueueGroupAll(): void
    {
        $job1 = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'group' => 'group-0',
        ]);

        $job2 = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'group' => 'group-1',
        ]);

        $job3 = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'group' => 'group-1',
        ]);

        $this->jobManager->process();

        $job1Reloaded = $this->entityManager->getEntityById('Job', $job1->getId());
        $job2Reloaded = $this->entityManager->getEntityById('Job', $job2->getId());
        $job3Reloaded = $this->entityManager->getEntityById('Job', $job3->getId());

        $this->assertEquals(Status::SUCCESS, $job1Reloaded->getStatus());
        $this->assertEquals(Status::SUCCESS, $job2Reloaded->getStatus());
        $this->assertEquals(Status::SUCCESS, $job3Reloaded->getStatus());
    }

    public function testProcessQueueGroupSeparate(): void
    {
        $job1 = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'group' => 'group-0',
        ]);

        $job2 = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'group' => 'group-1',
        ]);

        $job3 = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'group' => 'group-1',
        ]);

        $this->jobManager->processGroup('group-1', 100);

        $job1Reloaded = $this->entityManager->getEntityById('Job', $job1->getId());
        $job2Reloaded = $this->entityManager->getEntityById('Job', $job2->getId());
        $job3Reloaded = $this->entityManager->getEntityById('Job', $job3->getId());

        $this->assertEquals(Status::PENDING, $job1Reloaded->getStatus());
        $this->assertEquals(Status::SUCCESS, $job2Reloaded->getStatus());
        $this->assertEquals(Status::SUCCESS, $job3Reloaded->getStatus());

        $this->jobManager->processGroup('group-0', 100);

        $job1Reloaded2 = $this->entityManager->getEntityById('Job', $job1->getId());

        $this->assertEquals(Status::SUCCESS, $job1Reloaded2->getStatus());
    }

    public function testRunJobById(): void
    {
        $job = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
            'status' => Status::READY,
        ]);

        $this->jobManager->runJobById($job->getId());

        $jobReloaded = $this->entityManager->getEntityById('Job', $job->getId());

        $this->assertEquals(Status::SUCCESS, $jobReloaded->getStatus());
    }

    public function testRunJobByEntity(): void
    {
        $job = $this->entityManager->createEntity('Job', [
            'job' => 'Dummy',
        ]);

        $this->jobManager->runJob($job);

        $jobReloaded = $this->entityManager->getEntityById('Job', $job->getId());

        $this->assertEquals(Status::SUCCESS, $jobReloaded->getStatus());
    }

    public function testRunJobWithClassName(): void
    {
        $job = $this->entityManager->createEntity('Job', [
            'className' => TestJob::class,
            'data' => (object) [
                'test' => '1',
            ],
        ]);

        $this->jobManager->runJob($job);

        $jobReloaded = $this->entityManager->getEntityById('Job', $job->getId());

        $this->assertEquals(Status::SUCCESS, $jobReloaded->getStatus());
    }
}
