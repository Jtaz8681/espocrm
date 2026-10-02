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

namespace Espo\Core\Select\Where;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

use Espo\Entities\User;
use RuntimeException;

class ItemConverterFactory
{
    public function __construct(private InjectableFactory $injectableFactory, private Metadata $metadata)
    {}

    public function hasForType(string $type): bool
    {
        return (bool) $this->getClassNameForType($type);
    }

    public function createForType(string $type, string $entityType, User $user): ItemConverter
    {
        $className = $this->getClassNameForType($type);

        if (!$className) {
            throw new RuntimeException("Where item converter class name is not defined.");
        }

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $user);

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    /**
     * @return ?class-string<ItemConverter>
     */
    protected function getClassNameForType(string $type): ?string
    {
        return $this->metadata->get(['app', 'select', 'whereItemConverterClassNameMap', $type]);
    }

    public function has(string $entityType, string $attribute, string $type): bool
    {
        return (bool) $this->getClassName($entityType, $attribute, $type);
    }

    public function create(string $entityType, string $attribute, string $type, User $user): ItemConverter
    {
        $className = $this->getClassName($entityType, $attribute, $type);

        if (!$className) {
            throw new RuntimeException("Where item converter class name is not defined.");
        }

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);
        $binder
            ->bindInstance(User::class, $user);
        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    /**
     * @return ?class-string<ItemConverter>
     */
    protected function getClassName(string $entityType, string $attribute, string $type): ?string
    {
        return $this->metadata
            ->get(['selectDefs', $entityType, 'whereItemConverterClassNameMap', $attribute . '_' . $type]);
    }
}
