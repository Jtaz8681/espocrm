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

use DateTime;
use Espo\Core\Api\Request;
use Espo\Core\Api\Util;
use Espo\Core\Authentication\AuthenticationData;
use Espo\Core\Authentication\ConfigDataProvider;
use Espo\Core\Authentication\HeaderKey;
use Espo\Core\Authentication\Hook\BeforeLogin;
use Espo\Core\Authentication\Util\DelayUtil;
use Espo\Entities\AuthLogRecord;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Exception;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class UsernameFailedAttemptsLimit implements BeforeLogin
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private EntityManager $entityManager,
        private Util $util,
        private DelayUtil $delayUtil,
    ) {}

    public function process(AuthenticationData $data, Request $request): void
    {
        $isByTokenOnly = !$data->getMethod() && $request->getHeader(HeaderKey::AUTHORIZATION_BY_TOKEN) === 'true';

        if (
            $isByTokenOnly ||
            $this->configDataProvider->isAuthLogDisabled() ||
            !$this->configDataProvider->isUsernameFailedAttemptsLimitEnabled() ||
            $data->getUsername() === null
        ) {
            return;
        }

        $failedAttemptsPeriod = $this->configDataProvider->getUsernameFailedAttemptsPeriod();
        $delay = $this->configDataProvider->getUsernameFailedAttemptsDelay();

        $ipAddress = $this->util->obtainIpFromRequest($request);

        $repo = $this->entityManager->getRDBRepositoryByClass(AuthLogRecord::class);

        $where = [
            'username' => $data->getUsername(),
            'requestTime>' => $this->getTimeFrom($request, $failedAttemptsPeriod)->format('U'),
            'isDenied' => true,
        ];

        $wasFailed = (bool) $repo
            ->where($where)
            ->findOne();

        if (!$wasFailed) {
            return;
        }

        $failAttemptCount = $repo
            ->where($where)
            ->count();

        if ($failAttemptCount < $this->configDataProvider->getMaxUsernameFailedAttemptNumber()) {
            return;
        }

        if (
            // Prevent blocking for an IP address that has been logged in before.
            $ipAddress !== null &&
            $repo
                ->select([Attribute::ID])
                ->where([
                    'username' => $data->getUsername(),
                    'ipAddress' => $ipAddress,
                    'isDenied' => false,
                ])
                ->findOne()
        ) {
            return;
        }

        $this->delayUtil->delay($delay * 1000);
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
