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

namespace Espo\Core\Formula\Functions\EntityGroup;

use Espo\Core\Acl\SystemRestriction;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Exceptions\NotPassedEntity;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class IsRelatedType implements Func
{
    public function __construct(
        private SystemRestriction $systemRestriction,
        private EntityManager $entityManager,
        private ?Entity $entity = null,
    ) {}

    public function process(EvaluatedArgumentList $arguments): bool
    {
        $entity = $this->entity ?? throw new NotPassedEntity();

        if (!$entity instanceof CoreEntity) {
            throw new Error("Non-core entity.");
        }

        if (count($arguments) < 2) {
            throw TooFewArguments::create(1);
        }

        $link = $arguments[0];
        $id = $arguments[1];

        if (!is_string($link)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        $entityType = $entity->getEntityType();

        if (!$this->systemRestriction->checkLinkRead($entityType, $link)) {
            throw new NotAllowedUsage("Cannot read restricted field $entityType.$link.");
        }

        return $this->entityManager
            ->getRelation($entity, $link)
            ->isRelatedById($id);
    }
}
