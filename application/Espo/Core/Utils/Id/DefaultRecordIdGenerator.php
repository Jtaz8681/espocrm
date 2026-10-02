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

namespace Espo\Core\Utils\Id;

use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;

/**
 * Generates 17-character hex IDs.
 */
class DefaultRecordIdGenerator implements RecordIdGenerator
{
    private bool $isUuid;

    public function __construct(Metadata $metadata)
    {
        $this->isUuid =
            $metadata->get(['app', 'recordId', 'type']) === 'uuid4' ||
            $metadata->get(['app', 'recordId', 'dbType']) === 'uuid';
    }

    public function generate(): string
    {
        return $this->isUuid ?
            Util::generateUuid4() :
            Util::generateId();
    }
}
