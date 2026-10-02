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

namespace Espo\EntryPoints;

use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment as AttachmentEntity;
use Espo\Core\Acl;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\FileStorage\Manager as FileStorageManager;
use Espo\Core\ORM\EntityManager;

class Attachment implements EntryPoint
{
    public function __construct(
        private FileStorageManager $fileStorageManager,
        private EntityManager $entityManager,
        private Acl $acl,
        private Metadata $metadata
    ) {}

    public function run(Request $request, Response $response): void
    {
        $id = $request->getQueryParam('id');

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $attachment = $this->entityManager
            ->getRDBRepositoryByClass(AttachmentEntity::class)
            ->getById($id);

        if (!$attachment) {
            throw new NotFound("Attachment not found.");
        }

        if (!$this->acl->checkEntity($attachment)) {
            throw new Forbidden("No access to attachment.");
        }

        if (!$this->fileStorageManager->exists($attachment)) {
            throw new NotFound("File not found.");
        }

        $fileType = $attachment->getType();

        if (!in_array($fileType, $this->getAllowedFileTypeList())) {
            throw new Forbidden("Not allowed file type '{$fileType}'.");
        }

        if ($attachment->isBeingUploaded()) {
            throw new Forbidden("Attachment is being-uploaded.");
        }

        if ($fileType) {
            $response->setHeader('Content-Type', $fileType);
        }

        $stream = $this->fileStorageManager->getStream($attachment);

        $size = $stream->getSize() ?? $this->fileStorageManager->getSize($attachment);

        $response
            ->setHeader('Content-Length', (string) $size)
            ->setHeader('Cache-Control', 'private, max-age=864000, immutable')
            ->setHeader('Content-Security-Policy', "default-src 'self'; script-src 'none'; object-src 'none';")
            ->setBody($stream);
    }

    /**
     * @return string[]
     */
    private function getAllowedFileTypeList(): array
    {
        return $this->metadata->get(['app', 'image', 'allowedFileTypeList']) ?? [];
    }
}
