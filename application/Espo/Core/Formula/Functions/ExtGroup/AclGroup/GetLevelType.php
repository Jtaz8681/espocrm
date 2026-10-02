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
use Espo\Core\Formula\Exceptions\BadArgumentValue;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class GetLevelType implements Func
{
    public function __construct(
        private UserAclManagerProvider $userAclManagerProvider,
        private EntityManager $entityManager,
    ) {}

    public function process(EvaluatedArgumentList $arguments): string
    {
        if (count($arguments) < 3) {
            throw TooFewArguments::create(3);
        }

        $userId = $arguments[0];
        $scope = $arguments[1];
        $action = $arguments[2];

        if (!is_string($userId)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($scope)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!is_string($action)) {
            throw BadArgumentType::create(3, 'string');
        }

        if (
            !in_array($action, [
                Table::ACTION_READ,
                Table::ACTION_CREATE,
                Table::ACTION_EDIT,
                Table::ACTION_STREAM,
                Table::ACTION_DELETE,
            ])
        ) {
            throw BadArgumentValue::create(3);
        }

        /** @var Table::ACTION_* $action */

        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            return Table::LEVEL_NO;
        }

        return $this->userAclManagerProvider->get($user)->getLevel($user, $scope, $action);
    }
}
