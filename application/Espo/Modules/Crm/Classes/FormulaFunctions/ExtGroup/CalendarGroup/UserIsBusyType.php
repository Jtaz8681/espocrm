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

namespace Espo\Modules\Crm\Classes\FormulaFunctions\ExtGroup\CalendarGroup;

use Espo\Core\Field\DateTime;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Entities\User;
use Espo\Modules\Crm\Tools\Calendar\FreeBusy\FetchParams;
use Espo\Modules\Crm\Tools\Calendar\FreeBusy\Service;
use Espo\Modules\Crm\Tools\Calendar\Items\Event;
use Espo\ORM\EntityManager;
use Exception;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class UserIsBusyType implements Func
{
    public function __construct(
        private Service $service,
        private EntityManager $entityManager,
    ) {}

    public function process(EvaluatedArgumentList $arguments): bool
    {
        if (count($arguments) < 3) {
            throw TooFewArguments::create(3);
        }

        $userId = $arguments[0];
        $from = $arguments[1];
        $to = $arguments[2];
        $entityType = $arguments[3] ?? null;
        $id = $arguments[4] ?? null;

        if (!is_string($userId)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($from)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!is_string($to)) {
            throw BadArgumentType::create(3, 'string');
        }

        if ($entityType !== null && !is_string($entityType)) {
            throw BadArgumentType::create(4, 'string');
        }

        if ($id !== null && !is_string($id)) {
            throw BadArgumentType::create(5, 'string');
        }

        $ignoreList = [];

        if ($entityType && $id) {
            $ignoreList[] = (new Event(null, null, $entityType, []))->withId($id);
        }

        $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            throw new RuntimeException("User $userId not found.");
        }

        $busyParams = new FetchParams(
            from: DateTime::fromString($from),
            to: DateTime::fromString($to),
            ignoreEventList: $ignoreList,
        );

        try {
            $ranges = $this->service->fetchRanges($user, $busyParams);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $ranges !== [];
    }
}
