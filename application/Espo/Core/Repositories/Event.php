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

namespace Espo\Core\Repositories;

use Espo\Core\Field\Date;
use Espo\Core\Field\DateTime as DateTimeField;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Entities\Reminder;
use Espo\ORM\Entity;
use Espo\Core\Di;
use Espo\Core\Utils\DateTime as DateTimeUtil;

use DateTime;
use DateTimeZone;
use RuntimeException;
use Exception;

/**
 * @extends Database<CoreEntity>
 */
class Event extends Database implements

    Di\DateTimeAware,
    Di\ConfigAware
{
    use Di\DateTimeSetter;
    use Di\ConfigSetter;

    private const string FIELD_DURATION = 'duration';

    /**
     * @param array<string, mixed> $options
     * @return void
     */
    protected function beforeSave(Entity $entity, array $options = [])
    {
        if (
            $entity->isAttributeChanged(Meeting::FIELD_STATUS) &&
            in_array($entity->get(Meeting::FIELD_STATUS), $this->getNotActualStatuses())
        ) {
            $entity->set('reminders', []);
        }

        if (
            $entity->hasAttribute(Meeting::FIELD_IS_ALL_DAY) &&
            $entity->get(Meeting::FIELD_IS_ALL_DAY) === false
        ) {
            $entity->setMultiple([
                Meeting::FIELD_DATE_START_DATE => null,
                Meeting::FIELD_DATE_END_DATE => null,
            ]);
        }

        if ($entity->has(Meeting::FIELD_DATE_START_DATE)) {
            $dateStartDate = $entity->get(Meeting::FIELD_DATE_START_DATE);

            if (!empty($dateStartDate)) {
                $dateStart = $dateStartDate . ' 00:00:00';

                $dateStart = $this->convertDateTimeToDefaultTimezone($dateStart);

                $entity->set(Meeting::FIELD_DATE_START, $dateStart);
            } else {
                /** @noinspection PhpRedundantOptionalArgumentInspection */
                $entity->set(Meeting::FIELD_DATE_START_DATE, null);
            }
        }

        if ($entity->has(Meeting::FIELD_DATE_END_DATE)) {
            $dateEndDate = $entity->get(Meeting::FIELD_DATE_END_DATE);

            if (!empty($dateEndDate)) {
                try {
                    $dt = new DateTime(
                        $this->convertDateTimeToDefaultTimezone($dateEndDate . ' 00:00:00')
                    );
                } catch (Exception) {
                    throw new RuntimeException("Bad date-time.");
                }

                $dt->modify('+1 day');

                $dateEnd = $dt->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
                $entity->set(Meeting::FIELD_DATE_END, $dateEnd);
            } else {
                /** @noinspection PhpRedundantOptionalArgumentInspection */
                $entity->set(Meeting::FIELD_DATE_END_DATE, null);
            }
        }

        if ($entity->hasAttribute(self::FIELD_DURATION)) {
            if ($entity->get(Meeting::FIELD_DATE_START) && !$entity->get(Meeting::FIELD_DATE_END)) {
                $start = DateTimeField::fromString($entity->get(Meeting::FIELD_DATE_START));
                $duration = $entity->get(self::FIELD_DURATION) ?? 0;

                $end = $start->addSeconds($duration);

                $entity->set(Meeting::FIELD_DATE_END, $end->toString());
            }

            if ($entity->get(Meeting::FIELD_DATE_START_DATE) && !$entity->get(Meeting::FIELD_DATE_END_DATE)) {
                $start = Date::fromString($entity->get(Meeting::FIELD_DATE_START_DATE));
                $duration = $entity->get(self::FIELD_DURATION) ?? 0;

                $days = (int) floor($duration / (3600 * 24));

                $end = $start->addDays($days - 1);

                $entity->set(Meeting::FIELD_DATE_END_DATE, $end->toString());
            }
        }


        parent::beforeSave($entity, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @return void
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        parent::afterRemove($entity, $options);

        $delete = $this->entityManager->getQueryBuilder()
            ->delete()
            ->from(Reminder::ENTITY_TYPE)
            ->where([
                'entityId' => $entity->getId(),
                'entityType' => $entity->getEntityType(),
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($delete);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function convertDateTimeToDefaultTimezone($string)
    {
        $timeZone = $this->config->get('timeZone') ?? 'UTC';

        try {
            $tz = new DateTimeZone($timeZone);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        $dt = DateTime::createFromFormat(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT, $string, $tz);

        if ($dt === false) {
            throw new RuntimeException("Could not parse date-time `$string`.");
        }

        $utcTz = new DateTimeZone('UTC');

        return $dt
            ->setTimezone($utcTz)
            ->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
    }

    /**
     * @return string[]
     */
    private function getNotActualStatuses(): array
    {
        return array_merge(
            $this->metadata->get("scopes.$this->entityType.completedStatusList") ?? [],
            $this->metadata->get("scopes.$this->entityType.canceledStatusList") ?? [],
        );
    }
}
