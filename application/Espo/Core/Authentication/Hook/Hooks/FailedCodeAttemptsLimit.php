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

namespace Espo\Core\Authentication\Hook\Hooks;

use Espo\Core\Api\Request;
use Espo\Core\Authentication\AuthenticationData;
use Espo\Core\Authentication\ConfigDataProvider;
use Espo\Core\Authentication\Hook\BeforeLogin;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\AuthLogRecord;
use Espo\ORM\EntityManager;

use DateTime;
use Espo\ORM\Name\Attribute;
use Exception;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class FailedCodeAttemptsLimit implements BeforeLogin
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private EntityManager $entityManager,
    ) {}

    /**
     * @throws Forbidden
     */
    public function process(AuthenticationData $data, Request $request): void
    {
        if (
            $request->getHeader('Espo-Authorization-Code') === null ||
            $this->configDataProvider->isAuthLogDisabled()
        ) {
            return;
        }

        $isByTokenOnly = !$data->getMethod() && $request->getHeader('Espo-Authorization-By-Token') === 'true';

        if ($isByTokenOnly) {
            return;
        }

        $failedAttemptsPeriod = $this->configDataProvider->getFailedCodeAttemptsPeriod();

        $where = [
            'requestTime>' => $this->getTimeFrom($request, $failedAttemptsPeriod)->format('U'),
            'isDenied' => true,
            'username' => $data->getUsername(),
            'denialReason' => AuthLogRecord::DENIAL_REASON_WRONG_CODE,
        ];

        $wasFailed = (bool) $this->entityManager
            ->getRDBRepository(AuthLogRecord::ENTITY_TYPE)
            ->select([Attribute::ID])
            ->where($where)
            ->findOne();

        if (!$wasFailed) {
            return;
        }

        $failAttemptCount = $this->entityManager
            ->getRDBRepository(AuthLogRecord::ENTITY_TYPE)
            ->where($where)
            ->count();

        if ($failAttemptCount <= $this->configDataProvider->getMaxFailedAttemptNumber()) {
            return;
        }

        $username = $data->getUsername() ?? '';

        throw new Forbidden("Max failed 2FA login attempts exceeded for username '$username'.");
    }

    private function getTimeFrom(Request $request, string $failedAttemptsPeriod): DateTime
    {
        $requestTime = intval($request->getServerParam('REQUEST_TIME_FLOAT'));

        try {
            $requestTimeFrom = (new DateTime('@' . $requestTime))->modify('-' . $failedAttemptsPeriod);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $requestTimeFrom;
    }
}
