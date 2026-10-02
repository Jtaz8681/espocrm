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

namespace Espo\Classes\RecordHooks\Attachment;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment;
use Espo\ORM\Entity;
use Espo\Tools\Attachment\Checker;
use Espo\Tools\Attachment\DetailsObtainer;

/**
 * @implements SaveHook<Attachment>
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private Config $config,
        private Metadata $metadata,
        private DetailsObtainer $detailsObtainer,
        private Checker $checker
    ) {}

    public function process(Entity $entity): void
    {
        $this->processStorage($entity);
        $this->processRole($entity);
        $this->processSize($entity);

        $this->checker->checkType($entity);
    }

    private function processStorage(Attachment $entity): void
    {
        $storage = $entity->getStorage();

        $availableStorageList = $this->config->get('attachmentAvailableStorageList') ?? [];

        if (
            $storage &&
            (
                !in_array($storage, $availableStorageList) ||
                !$this->metadata->get(['app', 'fileStorage', 'implementationClassNameMap', $storage])
            )
        ) {
            $entity->clear('storage');
        }
    }

    /**
     * @throws Forbidden
     */
    private function processSize(Attachment $entity): void
    {
        $size = $entity->getSize();

        $maxSize = $this->detailsObtainer->getUploadMaxSize($entity);

        // Checking not actual file size but a set value.
        if ($size && $size > $maxSize) {
            throw new Forbidden("Attachment size exceeds `attachmentUploadMaxSize`.");
        }
    }

    private function processRole(Attachment $entity): void
    {
        if (!$entity->getRole()) {
            $entity->setRole(Attachment::ROLE_ATTACHMENT);
        }
    }
}
