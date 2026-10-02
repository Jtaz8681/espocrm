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

namespace Espo\Core\Formula\Functions\ExtGroup\AclGroup;

use Espo\Core\Acl\Table;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class CheckEntityType implements Func
{
    public function __construct(
        private UserAclManagerProvider $userAclManagerProvider,
        private EntityManager $entityManager,
    ) {}

    public function process(EvaluatedArgumentList $arguments): bool
    {
        if (count($arguments) < 3) {
            throw TooFewArguments::create(3);
        }

        $userId = $arguments[0];
        $entityType = $arguments[1];
        $id = $arguments[2];

        /** @var Table::ACTION_*|null $action */
        $action = $arguments[3] ?? Table::ACTION_READ;

        if (!is_string($userId)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($entityType)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(3, 'string');
        }

        if (!is_string($action)) {
            throw BadArgumentType::create(4, 'string');
        }

        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            return false;
        }

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            return false;
        }

        return $this->userAclManagerProvider->get($user)->checkEntity($user, $entity, $action);
    }
}
