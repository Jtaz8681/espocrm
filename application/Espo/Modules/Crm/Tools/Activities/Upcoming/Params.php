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

namespace Espo\Modules\Crm\Tools\Activities\Upcoming;

class Params
{
    /**
     * @param ?string[] $entityTypeList
     */
    public function __construct(
        readonly public ?int $offset = null,
        readonly public ?int $maxSize = null,
        readonly public ?int $futureDays = null,
        readonly public ?array $entityTypeList = null,
        readonly public bool $includeShared = false,
    ) {}
}
