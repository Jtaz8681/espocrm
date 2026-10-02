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

use Espo\Core\Field\LinkParent;
use Espo\Core\ORM\Entity;
use Espo\Core\Record\ActionHistory\Action;

class ActionHistoryRecord extends Entity
{
    public const ENTITY_TYPE = 'ActionHistoryRecord';

    public const ACTION_CREATE = Action::CREATE;
    public const ACTION_READ = Action::READ;
    public const ACTION_UPDATE = Action::UPDATE;
    public const ACTION_DELETE = Action::DELETE;

    /**
     * @param Action::* $action
     */
    public function setAction(string $action): self
    {
        return $this->set('action', $action);
    }

    public function setUserId(string $userId): self
    {
        return $this->set('userId', $userId);
    }

    public function setIpAddress(?string $ipAddress): self
    {
        return $this->set('ipAddress', $ipAddress);
    }

    public function setAuthTokenId(?string $authTokenId): self
    {
        return $this->set('authTokenId', $authTokenId);
    }

    public function setAuthLogRecordId(?string $authLogRecordId): self
    {
        return $this->set('authLogRecordId', $authLogRecordId);
    }

    public function setTarget(LinkParent $target): self
    {
        $this->set('targetId', $target->getId());
        $this->set('targetType', $target->getEntityType());

        return $this;
    }
}
