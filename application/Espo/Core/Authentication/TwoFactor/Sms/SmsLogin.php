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

namespace Espo\Core\Authentication\TwoFactor\Sms;

use Espo\Core\Authentication\HeaderKey;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Log;
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

class SmsLogin implements Login
{
    public const NAME = 'Sms';

    public function __construct(
        private EntityManager $entityManager,
        private Util $util,
        private Log $log
    ) {}

    public function login(Result $result, Request $request): Result
    {
        $code = $request->getHeader(HeaderKey::AUTHORIZATION_CODE);

        $user = $result->getUser();

        if (!$user) {
            throw new RuntimeException("No user.");
        }

        if (!$code) {
            $user = $result->getUser();

            if (!$user) {
                throw new RuntimeException("No user.");
            }

            try {
                $this->util->sendCode($user);
            } catch (Forbidden $e) {
                $this->log->error("Could not send 2FA code for user {$user->getUserName()}. " . $e->getMessage());

                return Result::fail(FailReason::ERROR);
            }

            return Result::secondStepRequired($user, $this->getResultData());
        }

        if ($this->verifyCode($user, $code)) {
            return $result;
        }

        return Result::fail(FailReason::CODE_NOT_VERIFIED);
    }

    private function getResultData(): ResultData
    {
        return ResultData::createWithMessage('enterCodeSentBySms');
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

        return $this->util->verifyCode($user, $code);
    }

    private function getUserDataRepository(): UserDataRepository
    {
        /** @var UserDataRepository */
        return $this->entityManager->getRepository(UserData::ENTITY_TYPE);
    }
}
