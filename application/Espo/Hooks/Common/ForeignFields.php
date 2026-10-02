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

namespace Espo\Hooks\Common;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Defs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\Type\AttributeType;

/**
 * @implements AfterSave<Entity>
 * @noinspection PhpUnused
 */
class ForeignFields implements AfterSave
{
    public static int $order = 8;

    public function __construct(
        private Defs $defs,
        private EntityManager $entityManager
    ) {}


    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$options->get(SaveOption::API) || $entity->isNew()) {
            return;
        }

        $defs = $this->defs->getEntity($entity->getEntityType());

        $foreignList = array_filter(
            $entity->getAttributeList(), fn ($it) => $entity->getAttributeType($it) === AttributeType::FOREIGN);

        $relationList = array_map(
            fn ($it) => $defs->getAttribute($it)->getParam(AttributeParam::RELATION), $foreignList);
        $relationList = array_filter($relationList, fn ($it) => $entity->isAttributeChanged($it . 'Id'));
        $relationList = array_values($relationList);

        if ($relationList === []) {
            return;
        }

        $copy = $this->entityManager->getEntityById($entity->getEntityType(), $entity->getId());

        if (!$copy) {
            return;
        }

        foreach ($foreignList as $attribute) {
            $entity->set($attribute, $copy->get($attribute));
        }
    }
}
