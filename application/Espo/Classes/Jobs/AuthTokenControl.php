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

namespace Espo\Classes\Jobs;

use Espo\Entities\AuthToken;
use Espo\Entities\Portal;
use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use DateTime;

/**
 * @noinspection PhpUnused
 */
class AuthTokenControl implements JobDataLess
{
    private const LIMIT = 500;

    public function __construct(
        private Config $config,
        private EntityManager $entityManager
    ) {}

    public function run(): void
    {
        $lifetime = (int) ($this->config->get('authTokenLifetime', 0) * 60);
        $maxIdleTime = (int) ($this->config->get('authTokenMaxIdleTime', 0) * 60);

        $portalIds = [];

        /** @var iterable<Portal> $portals */
        $portals = $this->entityManager
            ->getRDBRepositoryByClass(Portal::class)
            ->find();

        foreach ($portals as $portal) {
            $portalIds[] = $portal->getId();
        }

        $this->process(null, $lifetime, $maxIdleTime, $portalIds);

        foreach ($portals as $portal) {
            $itemLifetime = $portal->get('authTokenLifetime') !== null ?
                (int) ($portal->get('authTokenLifetime') * 60) :
                $lifetime;

            $itemMaxIdleTime = $portal->get('authTokenMaxIdleTime') !== null ?
                (int) ($portal->get('authTokenMaxIdleTime') * 60) :
                $maxIdleTime;

            $this->process($portal->getId(), $itemLifetime, $itemMaxIdleTime);
        }
    }

    /**
     * @param string[] $ignorePortalIds
     */
    private function process(?string $portalId, int $lifetime, int $maxIdleTime, array $ignorePortalIds = []): void
    {
        if (!$lifetime && !$maxIdleTime) {
            return;
        }

        $whereClause = ['isActive' => true];

        if ($portalId) {
            $whereClause['portalId'] = $portalId;
        }

        if (!$portalId && $ignorePortalIds !== []) {
            $whereClause[] = [
                'OR' => [
                    ['portalId' => null],
                    ['portalId!=' => $ignorePortalIds],
                ]
            ];
        }

        if ($lifetime) {
            $dt = new DateTime();
            $dt->modify("-$lifetime minutes");

            $whereClause['createdAt<'] = $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
        }

        if ($maxIdleTime) {
            $dt = new DateTime();
            $dt->modify("-$maxIdleTime minutes");

            $whereClause['lastAccess<'] = $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
        }

        $tokenList = $this->entityManager
            ->getRDBRepository(AuthToken::ENTITY_TYPE)
            ->sth()
            ->where($whereClause)
            ->limit(0, self::LIMIT)
            ->find();

        foreach ($tokenList as $token) {
            $token->set('isActive', false);

            $this->entityManager->saveEntity($token);
        }
    }
}
