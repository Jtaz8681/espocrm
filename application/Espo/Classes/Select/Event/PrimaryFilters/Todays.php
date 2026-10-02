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

namespace Espo\Classes\Select\Event\PrimaryFilters;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Select\Primary\Filter;
use Espo\Core\Select\Helpers\UserTimeZoneProvider;
use Espo\Core\Select\Where\ConverterFactory;
use Espo\Core\Select\Where\Item;
use Espo\ORM\Query\SelectBuilder;
use Espo\Entities\User;
use RuntimeException;

class Todays implements Filter
{
    public function __construct(
        private User $user,
        private UserTimeZoneProvider $userTimeZoneProvider,
        private ConverterFactory $converterFactory,
        private string $entityType
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $item = Item::fromRaw([
            'type' => Item\Type::TODAY,
            'attribute' => 'dateStart',
            'timeZone' => $this->userTimeZoneProvider->get(),
            'dateTime' => true,
        ]);

        try {
            $whereItem = $this->converterFactory
                ->create($this->entityType, $this->user)
                ->convert($queryBuilder, $item);
        } catch (BadRequest $e) {
            throw new RuntimeException('', 0, $e);
        }

        $queryBuilder->where($whereItem);
    }
}
