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

namespace Espo\Core\Formula\Functions;

use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Processor;
use Espo\Core\Formula\Utils\EntityUtil;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;
use stdClass;

class SetAttributeType extends Base
{
    public function __construct(
        private EntityUtil $entityUtil,
        string $name,
        Processor $processor,
        ?Entity $entity = null,
        ?stdClass $variables = null,
    ) {
        parent::__construct(
            name: $name,
            processor: $processor,
            entity: $entity,
            variables: $variables,
        );
    }

    /**
     * @return mixed
     * @throws Error
     */
    public function process(stdClass $item)
    {
        if (count($item->value) < 2) {
            throw TooFewArguments::create(2);
        }

        $attribute = $this->evaluate($item->value[0]);

        if (!is_string($attribute)) {
            throw BadArgumentType::create(1, 'string');
        }

        if ($attribute === Attribute::ID) {
            throw new NotAllowedUsage("Not allowed to set `id` attribute.");
        }

        $value = $this->evaluate($item->value[1]);

        $entity = $this->getEntity();

        $entityType = $entity->getEntityType();

        if (in_array($attribute, $this->entityUtil->getWriteRestrictedAttributeList($entityType))) {
            throw new NotAllowedUsage("Cannot write $entityType.$attribute.");
        }

        $entity->set($attribute, $value);

        $this->entityUtil->assertUpdateAccess($entity);

        return $value;
    }
}
