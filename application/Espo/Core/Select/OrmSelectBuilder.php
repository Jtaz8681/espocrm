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

namespace Espo\Core\Select;

use Espo\ORM\Query\SelectBuilder as QueryBuilder;

/**
 * Need access to raw params for backward compatibility.
 * The legacy select manager operates with raw params.
 */
class OrmSelectBuilder extends QueryBuilder
{
    /**
     * @param array<string, mixed> $params
     */
    public function setRawParams(array $params): void
    {
        $this->params = $params;
    }
}
