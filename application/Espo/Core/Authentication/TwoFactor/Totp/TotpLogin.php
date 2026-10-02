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

namespace Espo\Core\Authentication\TwoFactor\Totp;

use Espo\Core\Authentication\HeaderKey;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Entities\UserData;
use Espo\Repositories\UserData as UserDataRepository;
use Espo\Core\Authentication\TwoFactor\Login;
use Espo\Core\Authentication\Result;
use Espo\Core\Authentication\Result\Data as ResultData;
use Espo\Core\Authentication\Result\FailReason;
use Espo\Core\Api\Request;

use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class TotpLogin implements Login
{
    public const NAME = 'Totp';

    public function __construct(
        private EntityManager $entityManager,
        private Util $totp
    ) {}

    public function login(Result $result, Request $request): Result
    {
        $code = $request->getHeader(HeaderKey::AUTHORIZATION_CODE);

        $user = $result->getUser();

        if (!$user) {
            throw new RuntimeException("No user.");
        }

        if (!$code) {
            return Result::secondStepRequired($user, $this->getResultData());
        }

        if ($this->verifyCode($user, $code)) {
            return $result;
        }

        return Result::fail(FailReason::CODE_NOT_VERIFIED);
    }

    private function getResultData(): ResultData
    {
        return ResultData::createWithMessage('enterTotpCode');
    }

    private function verifyCode(User $user, string $code): bool
    {
        $userData = $this->getUserDataRepository()->getByUserId($user->getId());

        if (!$userData) {
            return false;
        }

        if (!$userData->get('auth2FA')) {
            return false;
        }

        if ($userData->get('auth2FAMethod') !== self::NAME) {
            return false;
        }

        $secret = $userData->get('auth2FATotpSecret');

        if (!$secret) {
            return false;
        }

        return $this->totp->verifyCode($secret, $code);
    }

    private function getUserDataRepository(): UserDataRepository
    {
        /** @var UserDataRepository $repository */
        $repository = $this->entityManager->getRepository(UserData::ENTITY_TYPE);

        return $repository;
    }
}
