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

namespace Espo\Core\Portal\Container;

use Espo\Core\Container\ContainerConfiguration as BaseContainerConfiguration;

class ContainerConfiguration extends BaseContainerConfiguration
{
    /**
     * @return ?class-string
     */
    public function getLoaderClassName(string $name): ?string
    {
        $className = null;

        try {
            $className = $this->metadata->get(['app', 'portalContainerServices', $name, 'loaderClassName']);
        } catch (\Exception) {}

        if ($className && class_exists($className)) {
            return $className;
        }

        $className = 'Espo\Custom\Core\Portal\Loaders\\' . ucfirst($name);
        if (!class_exists($className)) {
            $className = 'Espo\Core\Portal\Loaders\\' . ucfirst($name);
        }

        if (class_exists($className)) {
            return $className;
        }

        return parent::getLoaderClassName($name);
    }

    /**
     * @return ?class-string
     */
    public function getServiceClassName(string $name): ?string
    {
        return $this->metadata->get(['app', 'portalContainerServices', $name, 'className']) ??
            parent::getServiceClassName($name);
    }

    /**
     * @return ?string[]
     */
    public function getServiceDependencyList(string $name): ?array
    {
        return
            $this->metadata->get(['app', 'portalContainerServices', $name, 'dependencyList']) ??
            parent::getServiceDependencyList($name);
    }

    public function isSettable(string $name): bool
    {
        return
            $this->metadata->get(['app', 'portalContainerServices', $name, 'settable']) ??
            parent::isSettable($name);
    }
}
