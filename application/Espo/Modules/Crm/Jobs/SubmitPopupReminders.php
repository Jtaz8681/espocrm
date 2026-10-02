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

use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\WebSocket\ConfigDataProvider;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Entities\Reminder;
use Espo\Core\Job\JobDataLess;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Core\Utils\Log;
use Espo\Core\WebSocket\Submission as WebSocketSubmission;

use Throwable;
use DateTime;

/**
 * @noinspection PhpUnused
 */
class SubmitPopupReminders implements JobDataLess
{
    private const REMINDER_PAST_HOURS = 24;

    public function __construct(
        private EntityManager $entityManager,
        private Config $config,
        private WebSocketSubmission $webSocketSubmission,
        private Log $log,
        private ConfigDataProvider $webSocketConfig,
    ) {}

    public function run(): void
    {
        if (!$this->webSocketConfig->isEnabled()) {
            return;
        }

        $dt = new DateTime();
        $now = $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $pastHours = $this->config->get('reminderPastHours', self::REMINDER_PAST_HOURS);

        $nowShifted = $dt
            ->modify('-' . $pastHours . ' hours')
            ->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $reminderList = $this->entityManager
            ->getRDBRepositoryByClass(Reminder::class)
            ->where([
                'type' => Reminder::TYPE_POPUP,
                'remindAt<=' => $now,
                'startAt>' => $nowShifted,
                'isSubmitted' => false,
            ])
            ->find();

        $submitData = [];

        foreach ($reminderList as $reminder) {
            $userId = $reminder->getUserId();
            $entityType = $reminder->getTargetEntityType();
            $entityId = $reminder->getTargetEntityId();

            if (
                !$userId ||
                !$entityType ||
                !$entityId ||
                !$this->entityManager->hasRepository($entityType)
            ) {
                $this->deleteReminder($reminder);

                continue;
            }

            $entity = $this->entityManager->getEntityById($entityType, $entityId);

            if (!$entity) {
                $this->deleteReminder($reminder);

                continue;
            }

            if (
                $entity instanceof CoreEntity &&
                $entity->hasLinkMultipleField('users') &&
                $entity->hasAttribute('usersColumns')
            ) {
                $status = $entity->getLinkMultipleColumn('users', 'status', $userId);

                if ($status === Meeting::ATTENDEE_STATUS_DECLINED) {
                    $this->deleteReminder($reminder);

                    continue;
                }
            }

            $dateField = 'dateStart';

            $entityDefs = $this->entityManager->getDefs()->getEntity($entityType);

            if ($entityDefs->hasField('reminders')) {
                $dateField = $entityDefs
                    ->getField('reminders')
                    ->getParam('dateField') ?? $dateField;
            }

            $submitData[$userId] ??= [];

            $submitData[$userId][] = [
                'id' => $reminder->getId(),
                'data' => (object) [
                    'id' => $entity->getId(),
                    'entityType' => $entityType,
                    'name' => $entity->get(Field::NAME),
                    'dateField' => $dateField,
                    'attributes' => (object) [
                        $dateField => $entity->get($dateField),
                        $dateField . 'Date' => $entity->get($dateField . 'Date'),
                    ],
                ],
            ];

            $reminder->set('isSubmitted', true);

            $this->entityManager->saveEntity($reminder);
        }

        foreach ($submitData as $userId => $list) {
            try {
                $this->webSocketSubmission->submit('popupNotifications.event', $userId, ['list' => $list]);
            } catch (Throwable $e) {
                $this->log->error('Job SubmitPopupReminders: [' . $e->getCode() . '] ' .$e->getMessage());
            }
        }
    }

    private function deleteReminder(Reminder $reminder): void
    {
        $this->entityManager
            ->getRDBRepository(Reminder::ENTITY_TYPE)
            ->deleteFromDb($reminder->getId());
    }
}
