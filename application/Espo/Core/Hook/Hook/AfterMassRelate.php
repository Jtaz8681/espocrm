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

namespace Espo\Core\Hook\Hook;

use Espo\ORM\Entity;
use Espo\ORM\Query\Select;
use Espo\ORM\Repository\Option\MassRelateOptions;

/**
 * An afterMassRelate hook.
 *
 * @template TEntity of Entity = Entity
 */
interface AfterMassRelate
{
    /**
     * Processed after an entity is mass-related. Called from within a repository.
     *
     * @param TEntity $entity An entity.
     * @param string $relationName A relation name.
     * @param Select $query A select query for records to be related.
     * @param array<string, mixed> $columnData Middle table role values.
     * @param MassRelateOptions $options Options.
     */
    public function afterMassRelate(
        Entity $entity,
        string $relationName,
        Select $query,
        array $columnData,
        MassRelateOptions $options
    ): void;
}
