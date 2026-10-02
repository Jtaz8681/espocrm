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

namespace Espo\ORM\Repository;

use Espo\ORM\Entity;
use Espo\ORM\Query\Select;

interface HookMediator
{
    /**
     * @param array<string, mixed> $options
     */
    public function beforeSave(Entity $entity, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function afterSave(Entity $entity, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function beforeRemove(Entity $entity, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function afterRemove(Entity $entity, array $options): void;

    /**
     * @param array<string, mixed>|null $columnData Role values.
     * @param array<string, mixed> $options
     */
    public function beforeRelate(
        Entity $entity,
        string $relationName,
        Entity $foreignEntity,
        ?array $columnData,
        array $options
    ): void;

    /**
     * @param array<string, mixed>|null $columnData Role values.
     * @param array<string, mixed> $options
     */
    public function afterRelate(
        Entity $entity,
        string $relationName,
        Entity $foreignEntity,
        ?array $columnData,
        array $options
    ): void;

    /**
     * @param array<string, mixed> $options
     */
    public function beforeUnrelate(Entity $entity, string $relationName, Entity $foreignEntity, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function afterUnrelate(Entity $entity, string $relationName, Entity $foreignEntity, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function beforeMassRelate(Entity $entity, string $relationName, Select $query, array $options): void;

    /**
     * @param array<string, mixed> $options
     */
    public function afterMassRelate(Entity $entity, string $relationName, Select $query, array $options): void;
}
