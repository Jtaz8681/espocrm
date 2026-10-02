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

namespace Espo\Core\FieldProcessing;

use Espo\Core\Acl;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Entities\User;
use Espo\ORM\Defs;
use Espo\ORM\Entity;

use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

/**
 * Processes loading special fields (before output).
 */
class ReadLoadProcessor
{
    /** @var array<string, Loader<Entity>[]> */
    private array $loaderListMapCache = [];

    private BindingContainer $bindingContainer;

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private Acl $acl,
        private User $user,
        private Defs $defs,
    ) {
        $this->bindingContainer = BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->bindInstance(Acl::class, $this->acl)
            ->build();
    }

    public function process(Entity $entity, ?Params $params = null): void
    {
        if (!$params) {
            $params = new Params();
        }

        foreach ($this->getLoaderList($entity->getEntityType()) as $processor) {
            $processor->process($entity, $params);
        }
    }

    /**
     * @return Loader<Entity>[]
     */
    private function getLoaderList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->loaderListMapCache)) {
            return $this->loaderListMapCache[$entityType];
        }

        $list = [];

        foreach ($this->getLoaderClassNameList($entityType) as $className) {
            $list[] = $this->createLoader($className);
        }

        $this->loaderListMapCache[$entityType] = $list;

        return $list;
    }

    /**
     * @return class-string<Loader<Entity>>[]
     */
    private function getLoaderClassNameList(string $entityType): array
    {
        $entityLevelList = $this->getEntityLevelClassNameList($entityType);

        $list = $this->metadata
            ->get(['app', 'fieldProcessing', 'readLoaderClassNameList']) ?? [];

        $additionalList = $this->metadata
            ->get(['recordDefs', $entityType, 'readLoaderClassNameList']) ?? [];

        /** @var class-string<Loader<Entity>>[] $list */
        $list = array_merge($list, $additionalList, $entityLevelList);

        return array_values(array_unique($list));
    }

    /**
     * @param class-string<Loader<Entity>> $className
     * @return Loader<Entity>
     */
    private function createLoader(string $className): Loader
    {
        return $this->injectableFactory->createWithBinding($className, $this->bindingContainer);
    }

    /**
     * @return class-string<Loader<Entity>>[]
     */
    private function getEntityLevelClassNameList(string $entityType): array
    {
        $entityLevelList = [];

        $fieldList = $this->defs->getEntity($entityType)->getFieldList();

        foreach ($fieldList as $fieldDefs) {
            $className = $fieldDefs->getParam('loaderClassName');

            if (!$className || in_array($className, $entityLevelList)) {
                continue;
            }

            $entityLevelList[] = $className;
        }

        return $entityLevelList;
    }
}
