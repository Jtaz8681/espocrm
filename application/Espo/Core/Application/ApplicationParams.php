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

namespace Espo\Core\Application;

use Espo\Core\Binding\BindingProcessor;

/**
 * @since 9.2.0
 */
readonly class ApplicationParams
{
    /**
     * Important. Use named parameters. Backward compatibility for parameter order is not guaranteed.
     *
     * @param bool $noErrorHandler Disable error handling for tests.
     * @param ?BindingProcessor $binding Additional DI binding for integration tests. Since v9.3.
     * @param ?array<string, object> $services Instances of services. Since 10.0.
     */
    public function __construct(
        public bool $noErrorHandler = false,
        public ?BindingProcessor $binding = null,
        public ?array $services = null,
    ) {}
}
