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

use Espo\Core\Utils\File\Manager as FileManager;

use Espo\Core\FileStorage\Factory as FileStorageFactory;
use Espo\Core\FileStorage\Storages\EspoUploadDir;
use Espo\Core\FileStorage\Local;

use Espo\Entities\Attachment;

use Espo\Core\FileStorage\AttachmentEntityWrapper;

use Espo\ORM\EntityManager;

use LogicException;

class RemoveUploadDirFile implements Job
{
    private FileManager $fileManager;

    private FileStorageFactory $fileStorageFactory;

    private EntityManager $entityManager;

    public function __construct(
        FileManager $fileManager,
        FileStorageFactory $fileStorageFactory,
        EntityManager $entityManager
    ) {
        $this->fileManager = $fileManager;
        $this->fileStorageFactory = $fileStorageFactory;
        $this->entityManager = $entityManager;
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

        if ($attachment->getStorage() === EspoUploadDir::NAME) {
            return;
        }

        $storage = $this->fileStorageFactory->create(EspoUploadDir::NAME);

        if (!$storage instanceof Local) {
            throw new LogicException();
        }

        $filePath = $storage->getLocalFilePath(
            new AttachmentEntityWrapper($attachment)
        );

        if (!$this->fileManager->isFile($filePath)) {
            return;
        }

        $this->fileManager->remove($filePath);
    }
}
