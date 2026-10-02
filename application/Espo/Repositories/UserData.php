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

namespace Espo\Repositories;

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Entities\User as UserEntity;
use Espo\Entities\UserData as UserDataEntity;
use Espo\Core\Repositories\Database;

/**
 * @internal Use Espo\Tools\User\UserDataProvider.
 * @extends Database<UserDataEntity>
 */
class UserData extends Database
{
    public function getByUserId(string $userId): ?UserDataEntity
    {
        /** @var ?UserDataEntity $userData */
        $userData = $this
            ->where(['userId' => $userId])
            ->findOne();

        if ($userData) {
            return $userData;
        }

        $user = $this->entityManager
            ->getRepository(UserEntity::ENTITY_TYPE)
            ->getById($userId);

        if (!$user) {
            return null;
        }

        $userData = $this->getNew();

        $userData->set('userId', $userId);

        $this->save($userData, [
            SaveOption::SILENT => true,
            SaveOption::SKIP_HOOKS => true,
        ]);

        return $userData;
    }
}
