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

namespace Espo\Modules\Crm\Jobs;

use Espo\Core\InjectableFactory;
use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Core\Utils\Log;
use Espo\Modules\Crm\Tools\Reminder\Sender\EmailReminder;
use Espo\Modules\Crm\Entities\Reminder;
use Throwable;
use DateTime;
use DateInterval;

class SendEmailReminders implements JobDataLess
{
    private const MAX_PORTION_SIZE = 10;

    public function __construct(
        private InjectableFactory $injectableFactory,
        private EntityManager $entityManager,
        private Config $config,
        private Log $log
    ) {}

    public function run(): void
    {
        $dt = new DateTime();

        $now = $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $nowShifted = $dt
            ->sub(new DateInterval('PT1H'))
            ->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $maxPortionSize = $this->config->get('emailReminderPortionSize') ?? self::MAX_PORTION_SIZE;

        $collection = $this->entityManager
            ->getRDBRepositoryByClass(Reminder::class)
            ->where([
                'type' => Reminder::TYPE_EMAIL,
                'remindAt<=' => $now,
                'startAt>' => $nowShifted,
            ])
            ->limit(0, $maxPortionSize)
            ->find();

        if (count($collection) === 0) {
            return;
        }

        $emailReminder = $this->injectableFactory->create(EmailReminder::class);

        foreach ($collection as $entity) {
            try {
                $emailReminder->send($entity);
            } catch (Throwable $e) {
                $this->log->error("Email reminder '{$entity->getId()}': " . $e->getMessage());
            }

            $this->entityManager
                ->getRDBRepository(Reminder::ENTITY_TYPE)
                ->deleteFromDb($entity->getId());
        }
    }
}
