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

namespace Espo\Tools\Attachment;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Utils\File\MimeType;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment;
use Espo\Core\ORM\Type\FieldType;

class Checker
{
    public function __construct(
        private Metadata $metadata,
        private MimeType $mimeType,
        private DetailsObtainer $detailsObtainer
    ) {}

    /**
     * Check a mine-type for allowance.
     *
     * @throws Forbidden
     */
    public function checkType(Attachment $attachment): void
    {
        $field = $attachment->getTargetField();
        $entityType = $attachment->getParentType() ?? $attachment->getRelatedType();

        if (!$field || !$entityType) {
            return;
        }

        if (
            $this->detailsObtainer->getFieldType($attachment) === FieldType::IMAGE ||
            $attachment->getRole() === Attachment::ROLE_INLINE_ATTACHMENT
        ) {
            $this->checkTypeImage($attachment);

            return;
        }

        $extension = strtolower(DetailsObtainer::getFileExtension($attachment) ?? '');

        $mimeType = $this->mimeType->getMimeTypeByExtension($extension) ??
            $attachment->getType();

        /** @var string[] $accept */
        $accept = $this->metadata->get(['entityDefs', $entityType, 'fields', $field, 'accept']) ?? [];

        if ($accept === []) {
            return;
        }

        $found = false;

        foreach ($accept as $token) {
            if (strtolower($token) === '.' . $extension) {
                $found = true;

                break;
            }

            if ($mimeType && MimeType::matchMimeTypeToAcceptToken($mimeType, $token)) {
                $found = true;

                break;
            }
        }

        if (!$found) {
            throw new ForbiddenSilent("Not allowed file type.");
        }
    }

    /**
     * Check a mime-time for allowance for an image.
     *
     * @throws Forbidden
     */
    public function checkTypeImage(Attachment $attachment, ?string $filePath = null): void
    {
        $extension = DetailsObtainer::getFileExtension($attachment) ?? '';

        $mimeType = $this->mimeType->getMimeTypeByExtension($extension);

        /** @var string[] $imageTypeList */
        $imageTypeList = $this->metadata->get(['app', 'image', 'allowedFileTypeList']) ?? [];

        if (!in_array($mimeType, $imageTypeList)) {
            throw new ForbiddenSilent("Not allowed file type.");
        }

        $setMimeType = $attachment->getType();

        if (strtolower($setMimeType ?? '') !== $mimeType) {
            throw new ForbiddenSilent("Passed type does not correspond to extension.");
        }

        $this->checkDetectedMimeType($attachment, $filePath);
    }

    /**
     * @throws Forbidden
     */
    private function checkDetectedMimeType(Attachment $attachment, ?string $filePath = null): void
    {
        // ext-fileinfo required, otherwise bypass.
        if (!class_exists('\finfo') || !defined('FILEINFO_MIME_TYPE')) {
            return;
        }

        /** @var ?string $contents */
        $contents = $attachment->get('contents');

        if (!$contents && !$filePath) {
            return;
        }

        $extension = DetailsObtainer::getFileExtension($attachment) ?? '';

        $mimeTypeList = $this->mimeType->getMimeTypeListByExtension($extension);

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);

        $detectedMimeType = $filePath ?
            $fileInfo->file($filePath) :
            $fileInfo->buffer($contents);

        if (!in_array($detectedMimeType, $mimeTypeList)) {
            throw new ForbiddenSilent("Detected mime type does not correspond to extension.");
        }
    }
}
