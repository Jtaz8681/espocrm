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

namespace Espo\Entities;

use Espo\Core\ORM\Entity;
use Espo\ORM\EntityCollection;

class EmailTemplate extends Entity
{
    public const ENTITY_TYPE = 'EmailTemplate';

    public const STATUS_ACTIVE = 'Active';

    public function getSubject(): ?string
    {
        return $this->get('subject');
    }

    public function getBody(): ?string
    {
        return $this->get('body');
    }

    public function isHtml(): bool
    {
        return (bool) $this->get('isHtml');
    }

    /**
     * @return string[]
     */
    public function getAttachmentIdList(): array
    {
        /** @var string[] */
        return $this->getLinkMultipleIdList('attachments');
    }

    /**
     * @return EntityCollection<Attachment>
     */
    public function getAttachments(): EntityCollection
    {
        /** @var EntityCollection<Attachment> */
        return $this->relations->getMany('attachments');
    }
}
