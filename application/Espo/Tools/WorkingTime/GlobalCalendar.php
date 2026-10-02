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

namespace Espo\Tools\WorkingTime;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config;
use Espo\Entities\WorkingTimeCalendar;
use Espo\ORM\EntityManager;
use Espo\Tools\WorkingTime\Calendar\WorkingWeekday;
use Espo\Tools\WorkingTime\Calendar\WorkingDate;
use Espo\Core\Field\Date;

use DateTimeZone;
use Exception;
use RuntimeException;

class GlobalCalendar implements Calendar
{
    private ?WorkingTimeCalendar $workingTimeCalendar = null;
    private ?SpecificCalendar $specificCalendar = null;

    public function __construct(
        private EntityManager $entityManager,
        private Config $config,
        private InjectableFactory $injectableFactory,
        private Config\ApplicationConfig $applicationConfig,
    ) {
        $this->initDefault();

        if ($this->workingTimeCalendar) {
            $this->specificCalendar = $this->injectableFactory->createWithBinding(
                SpecificCalendar::class,
                BindingContainerBuilder::create()
                    ->bindInstance(WorkingTimeCalendar::class, $this->workingTimeCalendar)
                    ->build()
            );
        }
    }

    private function initDefault(): void
    {
        $id = $this->config->get('workingTimeCalendarId');

        if (!$id) {
            return;
        }

        $this->workingTimeCalendar = $this->entityManager->getEntityById(WorkingTimeCalendar::ENTITY_TYPE, $id);
    }

    /** @noinspection PhpUnused */
    public function isAvailable(): bool
    {
        if ($this->specificCalendar) {
            return $this->specificCalendar->isAvailable();
        }

        return false;
    }

    public function getTimezone(): DateTimeZone
    {
        if ($this->specificCalendar) {
            return $this->specificCalendar->getTimezone();
        }

        try {
            return new DateTimeZone($this->applicationConfig->getTimeZone());
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @return WorkingWeekday[]
     */
    public function getWorkingWeekdays(): array
    {
        if ($this->specificCalendar) {
            return $this->specificCalendar->getWorkingWeekdays();
        }

        return [];
    }

    /**
     * @return WorkingDate[]
     */
    public function getNonWorkingDates(Date $from, Date $to): array
    {
        if ($this->specificCalendar) {
            return $this->specificCalendar->getNonWorkingDates($from, $to);
        }

        return [];
    }

    /**
     * @return WorkingDate[]
     */
    public function getWorkingDates(Date $from, Date $to): array
    {
        if ($this->specificCalendar) {
            return $this->specificCalendar->getWorkingDates($from, $to);
        }

        return [];
    }
}
