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

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment;

class DetailsObtainer
{
    private Metadata $metadata;
    private Config $config;

    public function __construct(
        Metadata $metadata,
        Config $config
    ) {
        $this->metadata = $metadata;
        $this->config = $config;
    }

    /**
     * Get a file extension.
     */
    public static function getFileExtension(Attachment $attachment): ?string
    {
        $name = $attachment->getName() ?? '';

        return array_slice(explode('.', $name), -1)[0] ?? null;
    }

    /**
     * Get an upload max size allowed for an attachment (depending on a field it's related to).
     *
     * @return int A size in bytes.
     */
    public function getUploadMaxSize(Attachment $attachment): int
    {
        if ($attachment->getRole() === Attachment::ROLE_INLINE_ATTACHMENT) {
            return $this->config->get('inlineAttachmentUploadMaxSize') * 1024 * 1024;
        }

        $field = $attachment->getTargetField();
        $parentType = $attachment->getParentType() ?? $attachment->getRelatedType();

        if ($field && $parentType) {
            $maxSize = ($this->metadata
                ->get(['entityDefs', $parentType, 'fields', $field, 'maxFileSize']) ?? 0) * 1024 * 1024;

            if ($maxSize) {
                return $maxSize;
            }
        }

        return (int) $this->config->get('attachmentUploadMaxSize', 0) * 1024 * 1024;
    }

    /**
     * Get a field type (an attachment if related to another record through the field).
     */
    public function getFieldType(Attachment $attachment): ?string
    {
        $field = $attachment->getTargetField();
        $entityType = $attachment->getParentType() ?? $attachment->getRelatedType();

        if (!$field || !$entityType) {
            return null;
        }

        return $this->metadata->get(['entityDefs', $entityType, 'fields', $field, 'type']);
    }
}
