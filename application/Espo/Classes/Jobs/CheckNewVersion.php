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

use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Entities\Job;
use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;

use DateTime;
use DateTimeZone;

class CheckNewVersion implements JobDataLess
{
    public function __construct(
        protected Config $config,
        protected EntityManager $entityManager,
        private Config\ApplicationConfig $applicationConfig,
    ) {}

    public function run(): void
    {
        if (
            !$this->config->get('adminNotifications') ||
            !$this->config->get('adminNotificationsNewVersion')
        ) {
            return;
        }

        $className = \Espo\Tools\AdminNotifications\Jobs\CheckNewVersion::class;

        /** @todo Job scheduler is not used for bc reasons. */
        $this->entityManager->createEntity(Job::ENTITY_TYPE, [
            'name' => $className,
            'className' => $className,
            'executeTime' => $this->getRunTime(),
        ]);
    }

    protected function getRunTime(): string
    {
        $hour = rand(0, 4);
        $minute = rand(0, 59);

        $nextDay = new DateTime('+ 1 day');
        $time = $nextDay->format(DateTimeUtil::SYSTEM_DATE_FORMAT) . ' ' . $hour . ':' . $minute . ':00';

        $timeZone = $this->applicationConfig->getTimeZone();

        $datetime = new DateTime($time, new DateTimeZone($timeZone));

        return $datetime
            ->setTimezone(new DateTimeZone('UTC'))
            ->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
    }

    /**
     * For backward compatibility.
     * @deprecated
     */
    protected function getEntityManager() /** @phpstan-ignore-line */
    {
        return $this->entityManager;
    }
}
