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

namespace Espo\Core\Select\Order;

use Espo\Entities\User;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Binding\ContextualBinder;

use Espo\ORM\Defs\Params\FieldParam;
use RuntimeException;

class ItemConverterFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private User $user
    ) {}

    public function has(string $entityType, string $field): bool
    {
        return (bool) $this->getClassName($entityType, $field);
    }

    public function create(string $entityType, string $field): ItemConverter
    {
        $className = $this->getClassName($entityType, $field);

        if (!$className) {
            throw new RuntimeException("Order item converter class name is not defined.");
        }

        $container = BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->inContext($className, function (ContextualBinder $binder) use ($entityType) {
                $binder->bindValue('$entityType', $entityType);
            })
            ->build();

        return $this->injectableFactory->createWithBinding($className, $container);
    }

    /**
     * @return ?class-string<ItemConverter>
     */
    private function getClassName(string $entityType, string $field): ?string
    {
        /** @var ?class-string<ItemConverter> $className1 */
        $className1 = $this->metadata->get([
            'selectDefs', $entityType, 'orderItemConverterClassNameMap', $field
        ]);

        if ($className1) {
            return $className1;
        }

        $type = $this->metadata->get([
            'entityDefs', $entityType, 'fields', $field, FieldParam::TYPE
        ]);

        if (!$type) {
            return null;
        }

        /** @var ?class-string<ItemConverter> $className2 */
        $className2 = $this->metadata->get([
            'app', 'select', 'orderItemConverterClassNameMap', $type
        ]);

        if ($className2) {
            return $className2;
        }

        $className3 = 'Espo\\Core\\Select\\Order\\ItemConverters\\' . ucfirst($type) . 'Type';

        if (class_exists($className3)) {
            /** @var class-string<ItemConverter> */
            return $className3;
        }

        return null;
    }
}
