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

namespace Espo\Core\Record\Duplicator;

use Espo\Core\Acl;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Entities\User;
use Espo\ORM\Defs;
use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;

use RuntimeException;

class FieldDuplicatorFactory
{
    public function __construct(
        private Defs $defs,
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private Acl $acl,
        private User $user,
    ) {}

    public function create(string $entityType, string $field): FieldDuplicator
    {
        $className = $this->getClassName($entityType, $field);

        if (!$className) {
            throw new RuntimeException("No field duplicator for the field.");
        }

        return $this->injectableFactory->createWithBinding($className, $this->createBinding());
    }

    public function has(string $entityType, string $field): bool
    {
        return $this->getClassName($entityType, $field) !== null;
    }

    /**
     * @return ?class-string<FieldDuplicator>
     */
    private function getClassName(string $entityType, string $field): ?string
    {
        $fieldDefs = $this->defs
            ->getEntity($entityType)
            ->getField($field);

        $className1 = $fieldDefs->getParam('duplicatorClassName');

        if ($className1) {
            /** @var class-string<FieldDuplicator> */
            return $className1;
        }

        $type = $fieldDefs->getType();

        $className2 = $this->metadata->get(['fields', $type, 'duplicatorClassName']);

        if ($className2) {
            /** @var class-string<FieldDuplicator> */
            return $className2;
        }

        return null;
    }

    private function createBinding(): BindingContainer
    {
        return BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->bindInstance(Acl::class, $this->acl)
            ->build();
    }
}
