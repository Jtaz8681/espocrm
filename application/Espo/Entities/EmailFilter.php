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

class EmailFilter extends Entity
{
    public const ENTITY_TYPE = 'EmailFilter';

    public const ACTION_SKIP = 'Skip';
    public const ACTION_MOVE_TO_FOLDER = 'Move to Folder';
    public const ACTION_MOVE_TO_GROUP_FOLDER = 'Move to Group Folder';
    public const ACTION_NONE = 'None';

    public const STATUS_ACTIVE = 'Active';

    /**
     * @return self::ACTION_*|null
     */
    public function getAction(): ?string
    {
        return $this->get('action');
    }

    public function getEmailFolderId(): ?string
    {
        return $this->get('emailFolderId');
    }

    public function getGroupEmailFolderId(): ?string
    {
        return $this->get('groupEmailFolderId');
    }

    public function markAsRead(): bool
    {
        return (bool) $this->get('markAsRead');
    }

    public function skipNotification(): bool
    {
        return (bool) $this->get('skipNotification');
    }

    public function isGlobal(): bool
    {
        return (bool) $this->get('isGlobal');
    }

    public function getParentType(): ?string
    {
        return $this->get('parentType');
    }

    public function getParentId(): ?string
    {
        return $this->get('parentId');
    }

    public function getFrom(): ?string
    {
        return $this->get('from');
    }

    public function getTo(): ?string
    {
        return $this->get('to');
    }

    public function getSubject(): ?string
    {
        return $this->get('subject');
    }

    /**
     * @return string[]
     */
    public function getBodyContains(): array
    {
        return $this->get('bodyContains') ?? [];
    }

    /**
     * @return string[]
     */
    public function getBodyContainsAll(): array
    {
        return $this->get('bodyContainsAll') ?? [];
    }
}
