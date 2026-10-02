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

namespace Espo\Core\FieldProcessing\LinkParent;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;

use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @implements LoaderInterface<Entity>
 */
class TargetLoader implements LoaderInterface
{
    public function __construct(private EntityManager $entityManager)
    {}

    public function process(Entity $entity, Params $params): void
    {
        $targetType = $entity->get('targetType');
        $targetId = $entity->get('targetId');

        if (!$targetType || !$targetId) {
            return;
        }

        if (!$this->entityManager->hasRepository($targetType)) {
            return;
        }

        $query = $this->entityManager
            ->getQueryBuilder()
            ->select()
            ->from($targetType)
            ->withDeleted()
            ->where([
                Attribute::ID => $targetId,
            ])
            ->build();

        $target = $this->entityManager
            ->getRDBRepository($targetType)
            ->clone($query)
            ->findOne();

        if (!$target) {
            return;
        }

        if (!$target->get(Field::NAME)) {
            return;
        }

        $entity->set('targetName', $target->get(Field::NAME));
    }
}
