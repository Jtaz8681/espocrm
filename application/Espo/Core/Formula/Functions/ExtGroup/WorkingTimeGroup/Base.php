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

namespace Espo\Core\Formula\Functions\ExtGroup\WorkingTimeGroup;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\BadArgumentValue;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Functions\BaseFunction;

use Espo\Entities\Team;
use Espo\Entities\User;

use Espo\Core\Di;
use Espo\ORM\Entity;
use Espo\Tools\WorkingTime\Calendar;
use Espo\Tools\WorkingTime\CalendarFactory;
use Espo\Tools\WorkingTime\CalendarUtility;

abstract class Base extends BaseFunction implements

    Di\EntityManagerAware,
    Di\InjectableFactoryAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;

    private function getCalendarFactory(): CalendarFactory
    {
        return $this->injectableFactory->create(CalendarFactory::class);
    }

    protected function createCalendarUtility(Calendar $calendar): CalendarUtility
    {
        return $this->injectableFactory->createWithBinding(
            CalendarUtility::class,
            BindingContainerBuilder::create()
                ->bindInstance(Calendar::class, $calendar)
                ->build()
        );
    }

    /**
     * @param mixed[] $evaluatedArgs
     * @throws BadArgumentType
     * @throws BadArgumentValue
     * @throws Error
     */
    protected function createCalendar(array $evaluatedArgs, int $argumentPosition = 1): Calendar
    {
        $target = $this->obtainTarget($evaluatedArgs, $argumentPosition);

        if ($target instanceof User) {
            return $this->getCalendarFactory()->createForUser($target);
        }

        if ($target instanceof Team) {
            return $this->getCalendarFactory()->createForTeam($target);
        }

        return $this->getCalendarFactory()->createGlobal();
    }

    /**
     * @param mixed[] $evaluatedArgs
     * @throws BadArgumentType
     * @throws BadArgumentValue
     * @throws Error
     */
    private function obtainTarget(array $evaluatedArgs, int $argumentPosition = 1): ?Entity
    {
        if (count($evaluatedArgs) < $argumentPosition + 2) {
            return null;
        }

        $entityType = $evaluatedArgs[$argumentPosition];
        $entityId = $evaluatedArgs[$argumentPosition + 1];

        if (!is_string($entityType)) {
            $this->throwBadArgumentType($argumentPosition + 1, 'string');
        }

        if (!is_string($entityId)) {
            $this->throwBadArgumentType($argumentPosition + 2, 'string');
        }

        if (!in_array($entityType, [User::ENTITY_TYPE, Team::ENTITY_TYPE])) {
            $this->throwBadArgumentValue($argumentPosition + 1);
        }

        $entity = $this->entityManager->getEntityById($entityType, $entityId);

        if (!$entity) {
            $this->throwError("Entity {$entityType} {$entityId} not found.");
        }

        return $entity;
    }
}
