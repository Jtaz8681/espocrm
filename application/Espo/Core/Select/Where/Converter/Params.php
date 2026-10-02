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

namespace Espo\Core\Select\Where\Converter;

/**
 * Where converter parameters.
 *
 * Immutable.
 * @since 9.0.0
 */
class Params
{
    /**
     * @param bool $useSubQueryIfMany To use a sub-query if at least one has-many relation appears in a where clause.
     */
    public function __construct(
        readonly private bool $useSubQueryIfMany = false,
    ) {}

    /**
     * To use a sub-query if at least one has-many relation appears in a where clause.
     */
    public function useSubQueryIfMany(): bool
    {
        return $this->useSubQueryIfMany;
    }
}
