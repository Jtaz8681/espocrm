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

namespace Espo\Core\Container;

interface Configuration
{
    /**
     * @return ?class-string<Loader>
     */
    public function getLoaderClassName(string $name): ?string;

    /**
     * @return ?class-string<object>
     */
    public function getServiceClassName(string $name): ?string;

    /**
     * @return ?string[]
     */
    public function getServiceDependencyList(string $name): ?array;

    public function isSettable(string $name): bool;
}
