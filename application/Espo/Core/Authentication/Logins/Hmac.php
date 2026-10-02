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
use Espo\Core\Utils\ApiKey;

use RuntimeException;

class Hmac implements Login
{
    public const NAME = 'Hmac';

    public function __construct(private UserFinder $userFinder, private ApiKey $apiKeyUtil)
    {}

    public function login(Data $data, Request $request): Result
    {
        $authString = base64_decode($request->getHeader('X-Hmac-Authorization') ?? '');

        [$apiKey, $hash] = explode(':', $authString, 2);

        if (!$apiKey) {
            return Result::fail(FailReason::WRONG_CREDENTIALS);
        }

        $user = $this->userFinder->findApiHmac($apiKey);

        if (!$user) {
            return Result::fail(FailReason::WRONG_CREDENTIALS);
        }

        $secretKey = $this->apiKeyUtil->getSecretKeyForUserId($user->getId());

        if (!$secretKey) {
            throw new RuntimeException("No secret key for API user '" . $user->getId() . "'.");
        }

        $string = $request->getMethod() . ' ' . $request->getResourcePath();

        // As of v8.4.1.
        if ($hash === ApiKey::hash($secretKey, $string)) {
            return Result::success($user);
        }

        if ($hash === ApiKey::hashLegacy($secretKey, $string)) {
            return Result::success($user);
        }

        return Result::fail(FailReason::HASH_NOT_MATCHED);
    }
}
