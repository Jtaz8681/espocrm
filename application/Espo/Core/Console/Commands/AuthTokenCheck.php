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

namespace Espo\Core\Console\Commands;

use Espo\Entities\User;
use Espo\Core\Authentication\AuthToken\Manager as AuthTokenManager;
use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @noinspection PhpUnused
 */
class AuthTokenCheck implements Command
{
    public function __construct(private EntityManager $entityManager, private AuthTokenManager $authTokenManager)
    {}

    public function run(Params $params, IO $io): void
    {
        $io->setExitStatus(1);

        $token = $params->getArgument(0);
        $userId = $params->getArgument(1);

        if (!$token) {
            return;
        }

        $authToken = $this->authTokenManager->get($token);

        if (!$authToken) {
            return;
        }

        if (!$authToken->isActive()) {
            return;
        }

        if (!$authToken->getUserId()) {
            return;
        }

        if ($userId && $authToken->getUserId() !== $userId) {
            return;
        }

        $user = $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->select(Attribute::ID)
            ->where([
                Attribute::ID => $authToken->getUserId(),
                User::ATTR_IS_ACTIVE => true,
            ])
            ->findOne();

        if (!$user) {
            return;
        }

        $io->write($user->getId());
        $io->setExitStatus(0);
    }
}
