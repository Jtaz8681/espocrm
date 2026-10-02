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

use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;

use ReflectionClass;
use Exception;

class ContainerConfiguration implements Configuration
{
    /**
     * Log must be loaded before anything.
     * @noinspection PhpPropertyOnlyWrittenInspection
     * @phpstan-ignore-next-line
     */
    private Log $log;

    /**
     * Must be protected.
     */
    protected Metadata $metadata;

    public function __construct(Log $log, Metadata $metadata)
    {
        $this->log = $log;
        $this->metadata = $metadata;
    }

    /**
     * @return ?class-string
     */
    public function getLoaderClassName(string $name): ?string
    {
        $className = null;

        try {
            $className = $this->metadata->get(['app', 'containerServices', $name, 'loaderClassName']);
        } catch (Exception) {}

        if ($className && class_exists($className)) {
            return $className;
        }

        $className = 'Espo\Custom\Core\Loaders\\' . ucfirst($name);

        if (!class_exists($className)) {
            $className = 'Espo\Core\Loaders\\' . ucfirst($name);
        }

        if (class_exists($className)) {
            $class = new ReflectionClass($className);

            if ($class->isInstantiable()) {
                return $className;
            }
        }

        return null;
    }

    /**
     * @return ?class-string
     */
    public function getServiceClassName(string $name): ?string
    {
        return $this->metadata->get(['app', 'containerServices', $name, 'className']) ?? null;
    }

    /**
     * @return ?string[]
     */
    public function getServiceDependencyList(string $name): ?array
    {
        return $this->metadata->get(['app', 'containerServices', $name, 'dependencyList']) ?? null;
    }

    public function isSettable(string $name): bool
    {
        return $this->metadata->get(['app', 'containerServices', $name, 'settable']) ?? false;
    }
}
