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
class CheckScopeType implements Func
{
    public function __construct(
        private UserAclManagerProvider $userAclManagerProvider,
        private EntityManager $entityManager,
    ) {}

    public function process(EvaluatedArgumentList $arguments): bool
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $userId = $arguments[0];
        $scope = $arguments[1];
        $action = $arguments[2] ?? null;

        if (!is_string($userId)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($scope)) {
            throw BadArgumentType::create(2, 'string');
        }

        if ($action !== null && !is_string($action)) {
            throw BadArgumentType::create(3, 'string');
        }

        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            return false;
        }

        return $this->userAclManagerProvider->get($user)->checkScope($user, $scope, $action);
    }
}
