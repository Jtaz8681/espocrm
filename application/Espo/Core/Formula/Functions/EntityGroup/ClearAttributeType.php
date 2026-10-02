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

/**
 * @noinspection PhpUnused
 */
class ClearAttributeType implements Func
{
    public function __construct(
        private SystemRestriction $systemRestriction,
        private ?Entity $entity = null,
    ) {}

    public function process(EvaluatedArgumentList $arguments): null
    {
        $entity = $this->entity ?? throw new NotPassedEntity();

        if (!$entity instanceof CoreEntity) {
            throw new Error("Non-core entity.");
        }

        if (count($arguments) < 1) {
            throw TooFewArguments::create(1);
        }

        $attribute = $arguments[0];

        if (!is_string($attribute)) {
            throw BadArgumentType::create(1, 'string');
        }

        $entityType = $entity->getEntityType();

        if (!$this->systemRestriction->checkAttributeWrite($entityType, $attribute)) {
            throw new NotAllowedUsage("Cannot write restricted attribute $entityType.$attribute.");
        }

        $entity->clear($attribute);

        return null;
    }
}
