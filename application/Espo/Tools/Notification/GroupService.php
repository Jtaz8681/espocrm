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

namespace Espo\Tools\Notification;

use Espo\Core\Record\Collection as RecordCollection;
use Espo\Entities\Notification;
use Espo\Entities\User;
use Espo\ORM\Collection;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

class GroupService
{
    private const LIMIT = 100;

    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private RecordService $recordService,
    ) {}

    /**
     * @return RecordCollection<Notification>
     */
    public function get(Notification $notification): RecordCollection
    {
        if (!$notification->getActionId()) {
            /** @var Collection<Notification> $collection */
            $collection = $this->entityManager->getCollectionFactory()->create(Notification::ENTITY_TYPE);

            return RecordCollection::create($collection, 0);
        }

        $collection = $this->entityManager
            ->getRDBRepositoryByClass(Notification::class)
            ->where([
                Attribute::ID . '!=' => $notification->getId(),
                Notification::ATTR_ACTION_ID => $notification->getActionId(),
                Notification::ATTR_USER_ID => $this->user->getId(),
            ])
            ->limit(0, self::LIMIT)
            ->order(Notification::ATTR_NUMBER)
            ->find();

        $collection = $this->recordService->prepareCollection($collection, $this->user);

        return RecordCollection::create($collection, count($collection));
    }
}
