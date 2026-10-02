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

namespace Espo\Core\HttpClient;

use Espo\Core\HttpClient\Options\InternalHostRestriction;
use Espo\Core\HttpClient\Options\Redirect;

readonly class Options
{
    /**
     * @todo SSL options.
     * Use named parameters when calling.
     *
     * @param Protocol[] $protocols
     */
    public function __construct(
        public array $protocols = [Protocol::https, Protocol::http],
        public Redirect $redirect = new Redirect(),
        public ?int $timeout = null,
        public ?int $connectTimeout = null,
        public InternalHostRestriction $internalHostRestriction = new InternalHostRestriction(),
    ) {}
}
