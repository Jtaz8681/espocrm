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

namespace Espo\Tools\User;

use Espo\Entities\UserData;
use Espo\ORM\EntityManager;
use Espo\Repositories\UserData as UserDataRepository;
use RuntimeException;

class UserDataProvider
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function get(string $userId): ?UserData
    {
        return $this->getRepository()->getByUserId($userId);
    }

    private function getRepository(): UserDataRepository
    {
        $repository = $this->entityManager->getRepository(UserData::ENTITY_TYPE);

        if (!$repository instanceof UserDataRepository) {
            throw new RuntimeException();
        }

        return $repository;
    }
}
