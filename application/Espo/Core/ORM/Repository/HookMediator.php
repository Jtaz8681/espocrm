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

namespace Espo\Core\ORM\Repository;

use Espo\ORM\Entity;
use Espo\ORM\Query\Select;
use Espo\ORM\Repository\EmptyHookMediator;
use Espo\Core\HookManager;
use Espo\Core\ORM\Repository\Option\SaveOption;

class HookMediator extends EmptyHookMediator
{
    public function __construct(protected HookManager $hookManager)
    {}

    /**
     * @param ?array<string, mixed> $columnData
     * @param array<string, mixed> $options
     */
    public function afterRelate(
        Entity $entity,
        string $relationName,
        Entity $foreignEntity,
        ?array $columnData,
        array $options
    ): void {

        if (!empty($options[SaveOption::SKIP_HOOKS])) {
            return;
        }

        $hookData = [
            'relationName' => $relationName,
            'relationData' => $columnData,
            'foreignEntity' => $foreignEntity,
            'foreignId' => $foreignEntity->getId(),
        ];

        $this->hookManager->process(
            $entity->getEntityType(),
            'afterRelate',
            $entity,
            $options,
            $hookData
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterUnrelate(Entity $entity, string $relationName, Entity $foreignEntity, array $options): void
    {
        if (!empty($options[Option\SaveOption::SKIP_HOOKS])) {
            return;
        }

        $hookData = [
            'relationName' => $relationName,
            'foreignEntity' => $foreignEntity,
            'foreignId' => $foreignEntity->getId(),
        ];

        $this->hookManager->process(
            $entity->getEntityType(),
            'afterUnrelate',
            $entity,
            $options,
            $hookData
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterMassRelate(Entity $entity, string $relationName, Select $query, array $options): void
    {
        if (!empty($options[SaveOption::SKIP_HOOKS])) {
            return;
        }

        $hookData = [
            'relationName' => $relationName,
            'query' => $query,
        ];

        $this->hookManager->process(
            $entity->getEntityType(),
            'afterMassRelate',
            $entity,
            $options,
            $hookData
        );
    }
}
