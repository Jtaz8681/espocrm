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

namespace Espo\Tools\Stream;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error\Body as ErrorBody;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\Collection;
use Espo\Core\Select\SearchParams;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Core\Acl;

class FollowerRecordService
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private Acl $acl,
        private Metadata $metadata,
        private Service $service
    ) {}

    /**
     * Find followers.
     *
     * @return Collection<User>
     * @throws Forbidden
     * @throws NotFound
     * @throws BadRequest
     */
    public function find(string $entityType, string $id, SearchParams $params): Collection
    {
        $this->checkReadAccess($entityType);

        $entity = $this->getEntity($entityType, $id);

        return $this->service->findEntityFollowers($entity, $params);
    }

    /**
     * Add a user to followers.
     *
     * @throws NotFound
     * @throws Forbidden
     */
    public function link(string $entityType, string $id, string $userId): void
    {
        $this->checkEditAccess($entityType);

        $entity = $this->getEntityForEdit($entityType, $id);
        $user = $this->getUser($userId);

        $this->follow($entity, $user);
    }

    /**
     * Remove a user from followers.
     *
     * @throws NotFound
     * @throws Forbidden
     */
    public function unlink(string $entityType, string $id, string $userId): void
    {
        $this->checkEditAccess($entityType);

        $entity = $this->getEntityForEdit($entityType, $id);
        $user = $this->getUser($userId);

        $this->service->unfollowEntity($entity, $user->getId());
    }

    private function hasStream(string $entityType): bool
    {
        return (bool) $this->metadata->get(['scopes', $entityType, 'stream']);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function checkReadAccess(string $entityType): void
    {
        if (!$this->acl->check($entityType, Acl\Table::ACTION_READ)) {
            throw new Forbidden("No 'read'' access to $entityType scope.");
        }

        if (!$this->hasStream($entityType)) {
            throw new NotFound("No stream.");
        }
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function checkEditAccess(string $entityType): void
    {
        if (!$this->acl->check($entityType, Acl\Table::ACTION_EDIT)) {
            throw new Forbidden("No 'edit' access to $entityType scope.");
        }

        if (!$this->hasStream($entityType)) {
            throw new NotFound("No stream.");
        }
    }

    /**
     * @throws Forbidden
     */
    private function follow(Entity $entity, User $user): void
    {
        $result = $this->service->followEntity($entity, $user->getId());

        if ($result) {
            return;
        }

        throw Forbidden::createWithBody(
            "Could not add user to followers.",
            ErrorBody::create()->withMessageTranslation(
                'couldNotAddFollowerUserHasNoAccessToStream',
                'Stream',
                ['userName' => $user->getUserName() ?? '']
            )
        );
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     */
    private function getEntity(string $entityType, string $id): Entity
    {
        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            throw new NotFound("Record not found.");
        }

        if (!$this->acl->check($entity, Acl\Table::ACTION_READ)) {
            throw new Forbidden("No 'read' access.");
        }

        return $entity;
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     */
    private function getEntityForEdit(string $entityType, string $id): Entity
    {
        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            throw new NotFound("Record not found.");
        }

        if (!$this->acl->check($entity, Acl\Table::ACTION_EDIT)) {
            throw new Forbidden("No 'edit' access.");
        }

        if (!$this->acl->check($entity, Acl\Table::ACTION_STREAM)) {
            throw new Forbidden("No 'stream' access.");
        }

        return $entity;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function getUser(string $userId): User
    {
        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            throw new NotFound("User $userId not found.");
        }

        if (!$user->isPortal() && !$this->acl->check($user, Acl\Table::ACTION_READ)) {
            throw new Forbidden("No 'read' access to user $userId.");
        }

        if ($user->isPortal() && $this->acl->getPermissionLevel(Acl\Permission::PORTAL) !== Acl\Table::LEVEL_YES) {
            throw new Forbidden("No 'portal' permission.");
        }

        if (
            !$user->isPortal() &&
            $this->user->getId() !== $user->getId() &&
            !$this->acl->checkUserPermission($user, Acl\Permission::FOLLOWER_MANAGEMENT)
        ) {
            throw new Forbidden("No 'followerManagement' permission.");
        }

        return $user;
    }
}
