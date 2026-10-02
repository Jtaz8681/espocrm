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

use Espo\Core\ORM\Entity;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

/**
 * Processes saving special fields.
 */
class SaveProcessor
{
    /** @var array<string, Saver<Entity>[]> */
    private $saverListMapCache = [];

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public function process(Entity $entity, array $options): void
    {
        $params = Params::create()->withRawOptions($options);

        foreach ($this->getSaverList($entity->getEntityType()) as $processor) {
            $processor->process($entity, $params);
        }
    }

    /**
     * @return Saver<Entity>[]
     */
    private function getSaverList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->saverListMapCache)) {
            return $this->saverListMapCache[$entityType];
        }

        $list = [];

        foreach ($this->getSaverClassNameList($entityType) as $className) {
            $list[] = $this->createSaver($className);
        }

        $this->saverListMapCache[$entityType] = $list;

        return $list;
    }

    /**
     * @return class-string<Saver<Entity>>[]
     */
    private function getSaverClassNameList(string $entityType): array
    {
        $list = $this->metadata
            ->get(['app', 'fieldProcessing', 'saverClassNameList']) ?? [];

        $additionalList = $this->metadata
            ->get(['recordDefs', $entityType, 'saverClassNameList']) ?? [];

        /** @var class-string<Saver<Entity>>[] */
        return array_merge($list, $additionalList);
    }

    /**
     * @param class-string<Saver<Entity>> $className
     * @return Saver<Entity>
     */
    private function createSaver(string $className): Saver
    {
        return $this->injectableFactory->create($className);
    }
}
