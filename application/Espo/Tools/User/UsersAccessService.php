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

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Collection;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;

class UsersAccessService
{
    public function __construct(
        private SelectBuilderFactory $selectBuilderFactory,
        private Metadata $metadata,
        private Acl $acl,
        private User $user,
        private EntityManager $entityManager,
        private AclManager $aclManager,
        private ServiceContainer $serviceContainer,
    ) {}

    /**
     * @return Collection<User>
     * @throws Forbidden
     * @throws BadRequest
     */
    public function get(Entity $entity, SearchParams $params): Collection
    {
        $this->checkAccess($entity);
        $query = $this->prepareQuery($params);

        $repoBuilder = $this->entityManager->getRDBRepositoryByClass(User::class)->clone($query);

        $users = $repoBuilder->find();

        $service = $this->serviceContainer->getByClass(User::class);

        foreach ($users as $user) {
            $this->loadRecordAccessLevels($entity, $user);

            $service->prepareEntityForOutput($user);
        }

        return Collection::create($users, $repoBuilder->count());
    }

    /**
     * @throws Forbidden
     */
    private function checkAccess(Entity $entity): void
    {
        if ($this->user->isPortal()) {
            throw new Forbidden("No access for portal user.");
        }

        if (
            !$this->metadata->get("scopes.{$entity->getEntityType()}.object") &&
            !$this->metadata->get("scopes.{$entity->getEntityType()}.acl")
        ) {
            throw new Forbidden("Non-object entity and non-acl entity.");
        }

        if (!$this->acl->checkEntityRead($entity)) {
            throw new Forbidden("No record access.");
        }

        if (
            $this->acl->getPermissionLevel(Acl\Permission::USER) !== Acl\Table::LEVEL_TEAM &&
            $this->acl->getPermissionLevel(Acl\Permission::USER) !== Acl\Table::LEVEL_ALL
        ) {
            throw new Forbidden("No user permission.");
        }
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function prepareQuery(SearchParams $params): Select
    {
        $queryBuilder = $this->selectBuilderFactory
            ->create()
            ->from(User::ENTITY_TYPE)
            ->withStrictAccessControl()
            ->withSearchParams($params)
            ->buildQueryBuilder()
            ->where([
                'type' => [
                    User::TYPE_REGULAR,
                    User::TYPE_ADMIN,
                    User::TYPE_API,
                ]
            ]);

        if ($this->acl->getPermissionLevel(Acl\Permission::USER) === Acl\Table::LEVEL_TEAM) {
            $queryBuilder
                ->where(
                    Condition::in(
                        Expression::column('id'),
                        SelectBuilder::create()
                            ->from(Team::RELATIONSHIP_TEAM_USER)
                            ->select('userId')
                            ->where(['teamId' => $this->user->getTeams()->getIdList()])
                            ->build()
                    )
                );
        }

        return $queryBuilder->build();
    }

    private function loadRecordAccessLevels(Entity $entity, User $user): void
    {
        $actions = [
            Acl\Table::ACTION_READ,
            Acl\Table::ACTION_EDIT,
            Acl\Table::ACTION_DELETE,
            Acl\Table::ACTION_STREAM,
        ];

        $levels = [];

        foreach ($actions as $action) {
            $level = null;

            try {
                $level = $this->aclManager->checkEntity($user, $entity, $action);
            } catch (Acl\Exceptions\NotImplemented) {}

            $levels[$action] = $level;
        }

        $user->set('recordAccessLevels', $levels);
    }
}
