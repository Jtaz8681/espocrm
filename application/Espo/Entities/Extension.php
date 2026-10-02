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

use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

class Extension extends Entity
{
    public const ENTITY_TYPE = 'Extension';

    public const LICENSE_STATUS_VALID = 'Valid';
    public const LICENSE_STATUS_INVALID = 'Invalid';
    public const LICENSE_STATUS_EXPIRED = 'Expired';
    public const LICENSE_STATUS_SOFT_EXPIRED = 'Soft-Expired';

    public function getName(): string
    {
        return (string) $this->get(Field::NAME);
    }

    public function getVersion(): string
    {
        return (string) $this->get('version');
    }

    public function getLicenseStatusMessage(): ?string
    {
        return $this->get('licenseStatusMessage');
    }

    public function getLicenseStatus(): ?string
    {
        return $this->get('licenseStatus');
    }

    public function isInstalled(): bool
    {
        return (bool) $this->get('isInstalled');
    }
}
