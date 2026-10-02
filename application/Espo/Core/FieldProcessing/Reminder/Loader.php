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

namespace Espo\Core\FieldProcessing\Reminder;

use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Reminder;
use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

/**
 * @internal This class should not be removed as it's used by custom entities.
 * @implements LoaderInterface<Entity>
 */
class Loader implements LoaderInterface
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        $hasReminder = $this->entityManager
            ->getDefs()
            ->getEntity($entity->getEntityType())
            ->hasField('reminders');

        if (!$hasReminder) {
            return;
        }

        if ($params->hasSelect() && !$params->hasInSelect('reminders')) {
            return;
        }

        $entity->set('reminders', $this->fetchReminderDataList($entity));
    }

    /**
     * @return object{seconds: int, type: string}[]
     */
    private function fetchReminderDataList(Entity $entity): array
    {
        $list = [];

        /** @var iterable<Reminder> $collection */
        $collection = $this->entityManager
            ->getRDBRepository(Reminder::ENTITY_TYPE)
            ->select(['seconds', 'type'])
            ->where([
                'entityType' => $entity->getEntityType(),
                'entityId' => $entity->getId(),
                'userId' => $this->user->getId(),
            ])
            ->distinct()
            ->order('seconds')
            ->find();

        foreach ($collection as $reminder) {
            $list[] = (object) [
                'seconds' => $reminder->getSeconds(),
                'type' => $reminder->getType(),
            ];
        }

        return $list;
    }
}
