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

namespace Espo\Core\Utils\Database\Orm\IndexHelpers;

use Espo\Core\Utils\Database\Orm\IndexHelper;
use Espo\Core\Utils\Util;
use Espo\ORM\Defs\IndexDefs;

class MysqlIndexHelper implements IndexHelper
{
    private const MAX_LENGTH = 60;

    public function composeKey(IndexDefs $defs, string $entityType): string
    {
        $name = $defs->getName();
        $prefix = $defs->isUnique() ? 'UNIQ' : 'IDX';

        $parts = [$prefix, strtoupper(Util::toUnderScore($name))];

        $key = implode('_', $parts);

        return substr($key, 0, self::MAX_LENGTH);
    }
}
