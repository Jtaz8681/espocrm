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

namespace Espo\Tools\Attachment\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Core\Job\JobSchedulerFactory;

use Espo\Core\Field\DateTime;

use Espo\Core\FileStorage\Storages\EspoUploadDir;
use Espo\Core\Utils\Config;
use Espo\Core\FileStorage\Manager as FileStorageManager;

use Espo\ORM\EntityManager;

use Espo\Entities\Attachment;

use LogicException;

class MoveToStorage implements Job
{
    private const REMOVE_FILE_PERIOD = '3 hours';

    private EntityManager $entityManager;

    private Config $config;

    private FileStorageManager $fileStorageManager;

    private JobSchedulerFactory $jobSchedulerFactory;

    public function __construct(
        EntityManager $entityManager,
        Config $config,
        FileStorageManager $fileStorageManager,
        JobSchedulerFactory $jobSchedulerFactory
    ) {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->fileStorageManager = $fileStorageManager;
        $this->jobSchedulerFactory = $jobSchedulerFactory;
    }

    public function run(Data $data): void
    {
        $id = $data->getTargetId();

        if (!$id) {
            throw new LogicException();
        }

        /** @var Attachment|null $attachment */
        $attachment = $this->entityManager->getEntityById(Attachment::ENTITY_TYPE, $id);

        if (!$attachment) {
            return;
        }

        if ($attachment->getStorage() !== EspoUploadDir::NAME) {
            return;
        }

        $defaultFileStorage = $this->config->get('defaultFileStorage');

        if (!$defaultFileStorage || $defaultFileStorage === EspoUploadDir::NAME) {
            return;
        }

        $stream = $this->fileStorageManager->getStream($attachment);

        $attachment->set('storage', $defaultFileStorage);

        $this->fileStorageManager->putStream($attachment, $stream);

        $this->entityManager->saveEntity($attachment);

        $this->jobSchedulerFactory->create()
            ->setClassName(RemoveUploadDirFile::class)
            ->setData(
                Data::create()
                    ->withTargetId($attachment->getId())
            )
            ->setTime(
                DateTime::createNow()
                    ->modify('+' . self::REMOVE_FILE_PERIOD)
                    ->toDateTime()
            )
            ->schedule();
    }
}
