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

namespace Espo\Core\ORM;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Metadata as OrmMetadata;
use Espo\ORM\Value\ValueFactory;
use Espo\ORM\Value\ValueFactoryFactory as ValueFactoryFactoryInteface;

use RuntimeException;

class ValueFactoryFactory implements ValueFactoryFactoryInteface
{
    public function __construct(
        private Metadata $metadata,
        private OrmMetadata $ormMetadata,
        private InjectableFactory $injectableFactory
    ) {}

    public function isCreatable(string $entityType, string $field): bool
    {
        return $this->getClassName($entityType, $field) !== null;
    }

    public function create(string $entityType, string $field): ValueFactory
    {
        $className = $this->getClassName($entityType, $field);

        if (!$className) {
            throw new RuntimeException("Could not get ValueFactory for '{$entityType}.{$field}'.");
        }

        return $this->injectableFactory->create($className);
    }

    /**
     * @return ?class-string<ValueFactory>
     */
    private function getClassName(string $entityType, string $field): ?string
    {
        $fieldDefs = $this->ormMetadata
            ->getDefs()
            ->getEntity($entityType)
            ->getField($field);

        /** @var ?class-string<ValueFactory> $className */
        $className = $fieldDefs->getParam('valueFactoryClassName');

        if ($className) {
            return $className;
        }

        $type = $fieldDefs->getType();

        /** @var ?class-string<ValueFactory> */
        return $this->metadata->get(['fields', $type, 'valueFactoryClassName']);
    }
}
