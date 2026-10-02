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
use Espo\Core\Authentication\Helper\UserFinder;
use Espo\Core\Authentication\Login;
use Espo\Core\Authentication\Login\Data;
use Espo\Core\Authentication\Result;
use Espo\Core\Authentication\Result\FailReason;

class ApiKey implements Login
{
    public const NAME = 'ApiKey';

    private UserFinder $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    public function login(Data $data, Request $request): Result
    {
        $apiKey = $request->getHeader('X-Api-Key');

        if (!$apiKey) {
            return Result::fail(FailReason::WRONG_CREDENTIALS);
        }

        $user = $this->userFinder->findApiApiKey($apiKey);

        if (!$user) {
            return Result::fail(FailReason::WRONG_CREDENTIALS);
        }

        return Result::success($user);
    }
}
