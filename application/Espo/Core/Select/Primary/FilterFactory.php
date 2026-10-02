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

namespace Espo\Core\Select\Primary;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;

use RuntimeException;

class FilterFactory
{
    public function __construct(private InjectableFactory $injectableFactory, private Metadata $metadata)
    {}

    public function create(string $entityType, User $user, string $name): Filter
    {
        $className = $this->getClassName($entityType, $name);

        if (!$className) {
            throw new RuntimeException("Primary filter '{$name}' for '{$entityType}' does not exist.");
        }

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $user)
            ->for($className)
            ->bindValue('$entityType', $entityType)
            ->bindValue('$name', $name);

        $binder
            ->for(FieldHelper::class)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    public function has(string $entityType, string $name): bool
    {
        return (bool) $this->getClassName($entityType, $name);
    }

    /**
     * @return ?class-string<Filter>
     */
    protected function getClassName(string $entityType, string $name): ?string
    {
        if (!$name) {
            throw new RuntimeException("Empty primary filter name.");
        }

        $className = $this->metadata->get(
            [
                'selectDefs',
                $entityType,
                'primaryFilterClassNameMap',
                $name,
            ]
        );

        if ($className) {
            return $className;
        }

        return $this->getDefaultClassName($name);
    }

    /**
     * @return ?class-string<Filter>
     */
    protected function getDefaultClassName(string $name): ?string
    {
        $className = 'Espo\\Core\\Select\\Primary\\Filters\\' . ucfirst($name);

        if (!class_exists($className)) {
            return null;
        }

        /** @var class-string<Filter> */
        return $className;
    }
}
