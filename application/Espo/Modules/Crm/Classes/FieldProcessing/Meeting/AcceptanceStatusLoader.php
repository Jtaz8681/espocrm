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

namespace Espo\Modules\Crm\Classes\FieldProcessing\Meeting;

use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;

/**
 * @implements Loader<\Espo\Core\ORM\Entity>
 */
class AcceptanceStatusLoader implements Loader
{
    private EntityManager $entityManager;
    private User $user;

    private const ATTR_ACCEPTANCE_STATUS = 'acceptanceStatus';

    public function __construct(EntityManager $entityManager, User $user)
    {
        $this->entityManager = $entityManager;
        $this->user = $user;
    }

    public function process(Entity $entity, Params $params): void
    {
        if (!$params->hasInSelect(self::ATTR_ACCEPTANCE_STATUS)) {
            return;
        }

        if ($entity->has(self::ATTR_ACCEPTANCE_STATUS)) {
            return;
        }

        $attribute = self::ATTR_ACCEPTANCE_STATUS;

        $user = $this->entityManager
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, 'users')
            ->where([
                'id' => $this->user->getId(),
            ])
            ->select([$attribute])
            ->findOne();

        $value = null;

        if ($user) {
            $value = $user->get($attribute);
        }

        $entity->set(self::ATTR_ACCEPTANCE_STATUS, $value);
    }
}
