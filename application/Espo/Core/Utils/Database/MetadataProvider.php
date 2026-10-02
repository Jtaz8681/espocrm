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

namespace Espo\Core\Utils\Database;

use Doctrine\DBAL\Types\Types;
use Espo\Core\Utils\Metadata;

class MetadataProvider
{
    private const DEFAULT_ID_LENGTH = 24;
    private const DEFAULT_ID_DB_TYPE = Types::STRING;

    public function __construct(private Metadata $metadata)
    {}

    public function getIdLength(): int
    {
        return $this->metadata->get(['app', 'recordId', 'length']) ??
            self::DEFAULT_ID_LENGTH;
    }

    public function getIdDbType(): string
    {
        return $this->metadata->get(['app', 'recordId', 'dbType']) ??
            self::DEFAULT_ID_DB_TYPE;
    }
}
