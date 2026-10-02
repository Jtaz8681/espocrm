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

namespace Espo\Core\Formula\Functions\RecordGroup;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Formula\Utils\EntityUtil;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class DeleteType implements Func
{
    public function __construct(
        private EntityManager $entityManager,
        private EntityUtil $entityUtil,
    ) {}

    public function process(EvaluatedArgumentList $arguments): null
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $entityType = $arguments[0];
        $id = $arguments[1];

        if (!is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            return null;
        }

        $this->entityUtil->assertRemoveAccess($entity);

        $this->entityManager->removeEntity($entity);

        return null;
    }
}
