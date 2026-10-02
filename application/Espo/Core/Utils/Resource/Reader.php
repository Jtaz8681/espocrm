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

namespace Espo\Core\Utils\Resource;

use Espo\Core\Utils\File\Unifier;
use Espo\Core\Utils\File\UnifierObj;
use Espo\Core\Utils\Resource\Reader\Params;

use stdClass;

/**
 * Reads resource data. Reading is expensive. Read data is supposed to be cached after.
 */
class Reader
{
    public function __construct(
        private Unifier $unifier,
        private UnifierObj $unifierObj
    ) {}

    /**
     * Read resource data.
     */
    public function read(string $path, Params $params): stdClass
    {
        /** @var stdClass */
        return $this->unifierObj->unify($path, $params->noCustom(), $params->getForceAppendPathList());
    }

    /**
     * Read resource data as an associative array.
     *
     * @return array<string, mixed>
     */
    public function readAsArray(string $path, Params $params): array
    {
        /** @var array<string, mixed> */
        return $this->unifier->unify($path, $params->noCustom(), $params->getForceAppendPathList());
    }
}
