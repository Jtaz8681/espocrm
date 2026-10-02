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

use Espo\Entities\Team;
use Espo\ORM\Entity;
use Espo\Core\Repositories\Database;
use Espo\ORM\Name\Attribute;
use Espo\Repositories\UserData as UserDataRepository;
use Espo\Entities\UserData;
use Espo\Entities\User as UserEntity;

/**
 * @extends Database<UserEntity>
 */
class User extends Database
{
    private const AUTHENTICATION_METHOD_HMAC = 'Hmac';

    /**
     * @param UserEntity $entity
     * @param array<string, mixed> $options
     * @return void
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->has('type') && !$entity->getType()) {
            $entity->set('type', UserEntity::TYPE_REGULAR);
        }

        if ($entity->isApi()) {
            if ($entity->isAttributeChanged('userName')) {
                $entity->set('lastName', $entity->getUserName());
            }

            if ($entity->has('authMethod') && $entity->getAuthMethod() !== self::AUTHENTICATION_METHOD_HMAC) {
                $entity->clear('secretKey');
            }
        } else {
            if ($entity->isAttributeChanged('type')) {
                $entity->set('authMethod', null);
            }
        }

        parent::beforeSave($entity, $options);

        if ($entity->has('type') && !$entity->isPortal()) {
            $entity->set('portalRolesIds', []);
            $entity->set('portalRolesNames', (object) []);
            $entity->set('portalsIds', []);
            $entity->set('portalsNames', (object) []);
        }

        if ($entity->has('type') && $entity->isPortal()) {
            $entity->set('rolesIds', []);
            $entity->set('rolesNames', (object) []);
            $entity->set('teamsIds', []);
            $entity->set('teamsNames', (object) []);
            $entity->set('defaultTeamId', null);
            $entity->set('defaultTeamName', null);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return void
     */
    protected function afterSave(Entity $entity, array $options = [])
    {
        if ($this->entityManager->getLocker()->isLocked()) {
            $this->entityManager->getLocker()->commit();
        }

        parent::afterSave($entity, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @return void
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        parent::afterRemove($entity, $options);

        $userData = $this->getUserDataRepository()->getByUserId($entity->getId());

        if ($userData) {
            $this->entityManager->removeEntity($userData);
        }
    }

    /**
     * @param string[] $teamIds
     */
    public function checkBelongsToAnyOfTeams(string $userId, array $teamIds): bool
    {
        if ($teamIds === []) {
            return false;
        }

        return (bool) $this->entityManager
            ->getRDBRepository(Team::RELATIONSHIP_TEAM_USER)
            ->where([
                Attribute::DELETED => false,
                'userId' => $userId,
                'teamId' => $teamIds,
            ])
            ->findOne();
    }

    private function getUserDataRepository(): UserDataRepository
    {
        /** @var UserDataRepository */
        return $this->entityManager->getRepository(UserData::ENTITY_TYPE);
    }
}
