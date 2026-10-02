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

namespace Espo\Core\Authentication\Logins;

use Espo\Core\Api\Request;
use Espo\Core\Authentication\AuthToken\AuthToken;
use Espo\Core\Authentication\Helper\UserFinder;
use Espo\Core\Authentication\Login;
use Espo\Core\Authentication\Login\Data;
use Espo\Core\Authentication\Result;
use Espo\Core\Authentication\Result\FailReason;
use Espo\Core\Utils\PasswordHash;
use Espo\Entities\User;

class Espo implements Login
{
    public const string NAME = 'Espo';

    public function __construct(
        private UserFinder $userFinder,
        private PasswordHash $passwordHash
    ) {}

    public function login(Data $data, Request $request): Result
    {
        $username = $data->getUsername();
        $password = $data->getPassword();
        $authToken = $data->getAuthToken();

        if (!$username) {
            return Result::fail(FailReason::NO_USERNAME);
        }

        if (!$password) {
            return Result::fail(FailReason::NO_PASSWORD);
        }

        if ($authToken) {
            $user = $this->findUserByAuthTokenData($username, $authToken);
        } else {
            $user = $this->userFinder->find($username);

            if ($user && !$this->passwordHash->verify($password, $user->getPassword())) {
                $user = null;
            }
        }

        if (!$user) {
            return Result::fail(FailReason::WRONG_CREDENTIALS);
        }

        if ($authToken && $user->getId() !== $authToken->getUserId()) {
            return Result::fail(FailReason::USER_TOKEN_MISMATCH);
        }

        return Result::success($user);
    }


    private function findUserByAuthTokenData(string $username, AuthToken $authToken): ?User
    {
        return $this->userFinder
            ->findByAuthTokenData($username, $authToken->getUserId(), $authToken->getPasswordVersion());
    }
}
