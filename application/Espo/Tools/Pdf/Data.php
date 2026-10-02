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

namespace Espo\Tools\Pdf;

use stdClass;

class Data
{
    /** @var array<string, mixed> */
    private $additionalTemplateData = [];
    /** @var AttachmentWrapper[] */
    private $attachments = [];

    public function getAdditionalTemplateData(): stdClass
    {
        return (object) $this->additionalTemplateData;
    }

    public function withAdditionalTemplateData(stdClass $additionalTemplateData): self
    {
        $obj = clone $this;

        $obj->additionalTemplateData = array_merge(
            $obj->additionalTemplateData,
            get_object_vars($additionalTemplateData)
        );

        return $obj;
    }

    /**
     * @param AttachmentWrapper[] $attachments
     */
    public function withAttachmentsAdded(array $attachments): self
    {
        $obj = clone $this;

        foreach ($attachments as $attachment) {
            $obj->attachments[] = $attachment;
        }

        return $obj;
    }

    /**
     * @return AttachmentWrapper[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public static function create(): self
    {
        return new self();
    }
}
