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

namespace Espo\Tools\EmailTemplate;

use Espo\Core\Name\Field;
use Espo\Entities\Attachment;

use stdClass;

class Result
{
    /**
     * @param Attachment[] $attachmentList
     */
    public function __construct(
        private string $subject,
        private string $body,
        private bool $isHtml,
        private array $attachmentList = [],
    ) {}

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isHtml(): bool
    {
        return $this->isHtml;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachmentList(): array
    {
        return $this->attachmentList;
    }

    /**
     * @return string[]
     */
    public function getAttachmentIdList(): array
    {
        $list = [];

        foreach ($this->attachmentList as $attachment) {
            $list[] = $attachment->getId();
        }

        return $list;
    }

    public function getValueMap(): stdClass
    {
        $attachmentsIds = [];
        $attachmentsNames = (object) [];

        foreach ($this->attachmentList as $attachment) {
            $id = $attachment->getId();

            $attachmentsIds[] = $id;
            $attachmentsNames->$id = $attachment->get(Field::NAME);
        }

        return (object) [
            'subject' => $this->subject,
            'body' => $this->body,
            'isHtml' => $this->isHtml,
            'attachmentsIds' => $attachmentsIds,
            'attachmentsNames' => $attachmentsNames,
        ];
    }
}
