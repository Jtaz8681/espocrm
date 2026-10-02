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

class ConverterFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata
    ) {}

    public function create(string $entityType, User $user): Converter
    {
        $dateTimeItemTransformer = $this->createDateTimeItemTransformer($entityType, $user);

        $itemConverter = $this->createItemConverter($entityType, $user, $dateTimeItemTransformer);

        $className = $this->getConverterClassName($entityType);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);
        $binder
            ->bindInstance(User::class, $user);
        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType)
            ->bindInstance(ItemConverter::class, $itemConverter);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    private function createDateTimeItemTransformer(string $entityType, User $user): DateTimeItemTransformer
    {
        $className = $this->getDateTimeItemTransformerClassName($entityType);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);
        $binder->bindInstance(User::class, $user);
        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);
        $binder
            ->for(DefaultDateTimeItemTransformer::class)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    private function createItemConverter(
        string $entityType,
        User $user,
        DateTimeItemTransformer $dateTimeItemTransformer
    ): ItemConverter {

        $className = $this->getItemConverterClassName($entityType);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);
        $binder
            ->bindInstance(User::class, $user);
        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType)
            ->bindInstance(DateTimeItemTransformer::class, $dateTimeItemTransformer);
        $binder
            ->for(ItemGeneralConverter::class)
            ->bindValue('$entityType', $entityType)
            ->bindInstance(DateTimeItemTransformer::class, $dateTimeItemTransformer);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    /**
     * @return class-string<Converter>
     */
    private function getConverterClassName(string $entityType): string
    {
        $className = $this->metadata->get(['selectDefs', $entityType, 'whereConverterClassName']);

        if ($className) {
            return $className;
        }

        return Converter::class;
    }

    /**
     * @return class-string<ItemGeneralConverter>
     */
    private function getItemConverterClassName(string $entityType): string
    {
        $className = $this->metadata->get(['selectDefs', $entityType, 'whereItemConverterClassName']);

        if ($className) {
            return $className;
        }

        return ItemGeneralConverter::class;
    }

    /**
     * @return class-string<DateTimeItemTransformer>
     */
    private function getDateTimeItemTransformerClassName(string $entityType): string
    {
        $className = $this->metadata
            ->get(['selectDefs', $entityType, 'whereDateTimeItemTransformerClassName']);

        if ($className) {
            return $className;
        }

        return DefaultDateTimeItemTransformer::class;
    }
}
