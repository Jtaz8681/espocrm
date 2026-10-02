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
use Espo\Tools\Import\Params;

class Import extends Entity
{
    public const ENTITY_TYPE = 'Import';
    public const STATUS_STANDBY = 'Standby';
    public const STATUS_IN_PROCESS = 'In Process';
    public const STATUS_FAILED = 'Failed';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_COMPLETE = 'Complete';

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function getParams(): Params
    {
        $raw = $this->get('params');

        return Params::fromRaw($raw);
    }

    public function getFileId(): ?string
    {
        return $this->get('fileId');
    }

    public function getTargetEntityType(): ?string
    {
        return $this->get('entityType');
    }

    /**
     * @return ?string[]
     */
    public function getTargetAttributeList(): ?array
    {
        return $this->get('attributeList');
    }
}
