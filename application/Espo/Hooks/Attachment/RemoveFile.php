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

namespace Espo\Hooks\Attachment;

use Espo\Core\FileStorage\Manager as FileStorageManager;
use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\RemoveOptions;

/**
 * @implements AfterRemove<Attachment>
 */
class RemoveFile implements AfterRemove
{
    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
        private FileManager $fileManager,
        private FileStorageManager $fileStorageManager
    ) {}

    /**
     * @param Attachment $entity
     */
    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $duplicateCount = $this->entityManager
            ->getRDBRepositoryByClass(Attachment::class)
            ->where([
                'OR' => [
                    'sourceId' => $entity->getSourceId(),
                    'id' => $entity->getSourceId(),
                ]
            ])
            ->count();

        if ($duplicateCount) {
            return;
        }

        if ($this->fileStorageManager->exists($entity)) {
            $this->fileStorageManager->unlink($entity);
        }

        $this->removeThumbs($entity);
    }

    private function removeThumbs(Attachment $entity): void
    {
        /** @var string[] $typeList */
        $typeList = $this->metadata->get(['app', 'image', 'resizableFileTypeList']) ?? [];

        if (!in_array($entity->getType(), $typeList)) {
            return;
        }

        /** @var string[] $sizeList */
        $sizeList = array_keys($this->metadata->get(['app', 'image', 'sizes']) ?? []);

        foreach ($sizeList as $size) {
            $file = basename("{$entity->getSourceId()}_$size");

            $filePath = "data/upload/thumbs/$file";

            if ($this->fileManager->isFile($filePath)) {
                $this->fileManager->removeFile($filePath);
            }
        }
    }
}
