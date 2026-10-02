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

namespace Espo\Core\Formula\Functions\RecordServiceGroup;

use Espo\Core\Di\EntityManagerAware;
use Espo\Core\Di\EntityManagerSetter;
use Espo\Core\Di\RecordServiceContainerAware;
use Espo\Core\Di\RecordServiceContainerSetter;
use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Exceptions\WrapperException;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Json;

/**
 * @noinspection PhpUnused
 */
class ThrowDuplicateConflictType extends BaseFunction implements
    EntityManagerAware,
    RecordServiceContainerAware
{
    use EntityManagerSetter;
    use RecordServiceContainerSetter;

    public function process(ArgumentList $args)
    {
        if (empty($this->getVariables()->__isRecordService)) {
            $this->throwError("Can be called only from API script.");
        }

        if (count($args) < 1) {
            $this->throwTooFewArguments(1);
        }

        $ids = $this->evaluate($args[0]);

        if (is_string($ids)) {
            $ids = [$ids];
        }

        if (!is_array($ids)) {
            $this->throwBadArgumentType(1);
        }

        $entityType = $this->getEntity()->getEntityType();

        $list = [];

        foreach ($ids as $id) {
            $entity = $this->entityManager->getEntityById($entityType, $id);

            if ($entity) {
                $this->recordServiceContainer->get($entityType)->prepareEntityForOutput($entity);
            }

            if (!$entity) {
                $entity = $this->entityManager->getNewEntity($entityType);
                $entity->set(Field::NAME, $id);
            }

            $list[] = $entity->getValueMap();
        }

        throw WrapperException::create(
            ConflictSilent::createWithBody('duplicate', Json::encode($list))
        );
    }
}
