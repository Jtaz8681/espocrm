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

use Espo\Core\Api\Util;
use Espo\Core\Authentication\HeaderKey;
use Espo\Core\Authentication\Hook\BeforeLogin;
use Espo\Core\Authentication\AuthenticationData;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Authentication\ConfigDataProvider;
use Espo\ORM\EntityManager;
use Espo\Entities\AuthLogRecord;

use DateTime;
use Espo\ORM\Name\Attribute;
use Exception;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class FailedAttemptsLimit implements BeforeLogin
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private EntityManager $entityManager,
        private Util $util
    ) {}

    /**
     * @throws Forbidden
     */
    public function process(AuthenticationData $data, Request $request): void
    {
        $isByTokenOnly = !$data->getMethod() && $request->getHeader(HeaderKey::AUTHORIZATION_BY_TOKEN) === 'true';

        if ($isByTokenOnly || $this->configDataProvider->isAuthLogDisabled()) {
            return;
        }

        $failedAttemptsPeriod = $this->configDataProvider->getFailedAttemptsPeriod();

        $ipAddress = $this->util->obtainIpFromRequest($request);

        $where = [
            'requestTime>' => $this->getTimeFrom($request, $failedAttemptsPeriod)->format('U'),
            'isDenied' => true,
            'ipAddress' => $ipAddress,
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

        throw new Forbidden("Max failed login attempts exceeded for IP address $ipAddress.");
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
