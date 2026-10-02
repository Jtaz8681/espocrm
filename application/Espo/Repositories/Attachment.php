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

namespace Espo\Repositories;

use Espo\ORM\Entity;
use Espo\Entities\Attachment as AttachmentEntity;
use Espo\Core\Repositories\Database;
use Espo\Core\FileStorage\Storages\EspoUploadDir;
use Espo\Core\Di;

use Psr\Http\Message\StreamInterface;

/**
 * @extends Database<AttachmentEntity>
 */
class Attachment extends Database implements
    Di\FileStorageManagerAware,
    Di\ConfigAware
{
    use Di\FileStorageManagerSetter;
    use Di\ConfigSetter;

    /**
     * @param AttachmentEntity $entity
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        if ($entity->isNew()) {
            $this->processBeforeSaveNew($entity);
        }
    }

    protected function processBeforeSaveNew(AttachmentEntity $entity): void
    {
        if ($entity->isBeingUploaded()) {
            $entity->setStorage(EspoUploadDir::NAME);
        }

        if (!$entity->getStorage()) {
            $defaultStorage = $this->config->get('defaultFileStorage');

            $entity->setStorage($defaultStorage);
        }

        $contents = $entity->get('contents');

        if (is_null($contents)) {
            return;
        }

        if (!$entity->isBeingUploaded()) {
            $entity->setSize(strlen($contents));
        }

        $this->fileStorageManager->putContents($entity, $contents);
    }

    /**
     * Copy an attachment record (to reuse the same file w/o copying it in the storage).
     */
    public function getCopiedAttachment(AttachmentEntity $entity, ?string $role = null): AttachmentEntity
    {
        $new = $this->getNew();

        $new
            ->setSourceId($entity->getSourceId())
            ->setName($entity->getName())
            ->setType($entity->getType())
            ->setSize($entity->getSize())
            ->setRole($entity->getRole());

        if ($role) {
            $new->setRole($role);
        }

        $this->save($new);

        return $new;
    }

    public function getContents(AttachmentEntity $entity): string
    {
        return $this->fileStorageManager->getContents($entity);
    }

    public function getStream(AttachmentEntity $entity): StreamInterface
    {
        return $this->fileStorageManager->getStream($entity);
    }

    /**
     * A size in bytes.
     */
    public function getSize(AttachmentEntity $entity): int
    {
        return $this->fileStorageManager->getSize($entity);
    }

    public function getFilePath(AttachmentEntity $entity): string
    {
        return $this->fileStorageManager->getLocalFilePath($entity);
    }
}
