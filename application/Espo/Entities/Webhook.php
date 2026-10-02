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

class Webhook extends Entity
{
    public const ENTITY_TYPE = 'Webhook';

    public function getEvent(): string
    {
        return $this->get('event') ?? '';
    }

    public function getSecretKey(): ?string
    {
        return $this->get('secretKey');
    }

    public function getUrl(): ?string
    {
        return $this->get('url');
    }

    public function isActive(): bool
    {
        return $this->get('isActive');
    }

    public function getUserId(): ?string
    {
        return $this->get('userId');
    }

    public function getTargetEntityType(): string
    {
        return $this->get('entityType');
    }

    public function setSkipOwn(bool $skipOwn): self
    {
        return $this->set('skipOwn', $skipOwn);
    }

    public function skipOwn(): bool
    {
        return (bool) $this->get('skipOwn');
    }
}
