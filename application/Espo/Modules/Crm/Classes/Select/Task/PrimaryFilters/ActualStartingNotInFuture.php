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

namespace Espo\Modules\Crm\Classes\Select\Task\PrimaryFilters;

use Espo\Entities\User;

use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\Core\Select\Primary\Filter;
use Espo\Core\Select\Helpers\UserTimeZoneProvider;
use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\ConverterFactory;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Task;

class ActualStartingNotInFuture implements Filter
{
    public function __construct(
        private User $user,
        private UserTimeZoneProvider $userTimeZoneProvider,
        private Metadata $metadata,
        private ConverterFactory $converterFactory
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $notActualStatusList = $this->metadata
            ->get(['entityDefs', 'Task', 'fields', 'status', 'notActualOptions']) ?? [];

        $converter = $this->converterFactory->create(Task::ENTITY_TYPE, $this->user);

        $queryBuilder->where(
            Cond::and(
                Cond::notIn(
                    Cond::column('status'),
                    $notActualStatusList
                ),
                Cond::or(
                    Cond::equal(
                        Cond::column('dateStart'),
                        null
                    ),
                    Cond::and(
                        Cond::notEqual(
                            Cond::column('dateStart'),
                            null
                        ),
                        Cond::or(
                            $converter->convert(
                                $queryBuilder,
                                Item::fromRaw([
                                    'type' => Item\Type::PAST,
                                    'attribute' => 'dateStart',
                                    'timeZone' => $this->userTimeZoneProvider->get(),
                                    'dateTime' => true,
                                ])
                            ),
                            $converter->convert(
                                $queryBuilder,
                                Item::fromRaw([
                                    'type' => Item\Type::TODAY,
                                    'attribute' => 'dateStart',
                                    'timeZone' => $this->userTimeZoneProvider->get(),
                                    'dateTime' => true,
                                ])
                            )
                        )
                    )
                )
            )
        );
    }
}
