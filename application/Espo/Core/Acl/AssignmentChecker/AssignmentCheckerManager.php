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

namespace Espo\Core\Acl\AssignmentChecker;

use Espo\ORM\Entity;
use Espo\Entities\User;
use Espo\Core\Acl\AssignmentChecker;

class AssignmentCheckerManager
{
    /** @var array<string, AssignmentChecker<Entity>> */
    private $checkerCache = [];

    public function __construct(private AssignmentCheckerFactory $factory)
    {}

    public function check(User $user, Entity $entity): bool
    {
        $entityType = $entity->getEntityType();

        $checker = $this->getChecker($entityType);

        return $checker->check($user, $entity);
    }

    /**
     * @return AssignmentChecker<Entity>
     */
    private function getChecker(string $entityType): AssignmentChecker
    {
        if (!array_key_exists($entityType, $this->checkerCache)) {
            $this->loadChecker($entityType);
        }

        return $this->checkerCache[$entityType];
    }

    private function loadChecker(string $entityType): void
    {
        $this->checkerCache[$entityType] = $this->factory->create($entityType);
    }
}
