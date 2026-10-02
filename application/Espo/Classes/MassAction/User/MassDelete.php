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

namespace Espo\Classes\MassAction\User;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\MassAction\Actions\MassDelete as MassDeleteOriginal;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\QueryBuilder;
use Espo\Core\MassAction\Result;
use Espo\Core\ORM\EntityManager;

use Espo\Core\Utils\SystemUser;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;

/**
 * Extended to forbid removal of own and system users.
 */
class MassDelete implements MassAction
{
    public function __construct(
        private MassDeleteOriginal $massDeleteOriginal,
        private QueryBuilder $queryBuilder,
        private EntityManager $entityManager,
        private Acl $acl,
        private User $user
    ) {}

    /**
     * @throws Forbidden
     * @throws BadRequest
     * @throws Error
     */
    public function process(Params $params, Data $data): Result
    {
        $entityType = $params->getEntityType();

        if (!$this->acl->check($entityType, Acl\Table::ACTION_DELETE)) {
            throw new Forbidden("No delete access for '$entityType'.");
        }

        if (
            !$params->hasIds() &&
            $this->acl->getPermissionLevel(Acl\Permission::MASS_UPDATE) !== Acl\Table::LEVEL_YES
        ) {
            throw new Forbidden("No mass-update permission.");
        }

        $query = $this->queryBuilder->build($params);

        $collection = $this->entityManager
            ->getRDBRepository(User::ENTITY_TYPE)
            ->clone($query)
            ->sth()
            ->select([Attribute::ID, 'userName'])
            ->find();

        foreach ($collection as $entity) {
            $this->checkEntity($entity);
        }

        return $this->massDeleteOriginal->process($params, $data);
    }

    /**
     * @throws Forbidden
     */
    private function checkEntity(User $entity): void
    {
        if ($entity->getUserName() === SystemUser::NAME) {
            throw new Forbidden("Can't delete 'system' user.");
        }

        if ($entity->getId() === $this->user->getId()) {
            throw new Forbidden("Can't delete own user.");
        }
    }
}
