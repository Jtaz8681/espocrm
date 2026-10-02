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

namespace Espo\Classes\Cleanup;

use Espo\Core\Cleanup\Cleanup;
use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\Entities\StarSubscription;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\DeleteBuilder;
use Espo\Tools\Stars\StarService;

/**
 * @noinspection PhpUnused
 */
class Stars implements Cleanup
{
    public function __construct(
        private EntityManager $entityManager,
        private UserAclManagerProvider $userAclManagerProvider,
        private StarService $service
    ) {}

    public function process(): void
    {
        foreach ($this->getEntityTypeList() as $entityType) {
            $this->processEntityType($entityType);
        }
    }

    /**
     * @return string[]
     */
    private function getEntityTypeList(): array
    {
        $groups = $this->entityManager->getRDBRepositoryByClass(StarSubscription::class)
            ->group('entityType')
            ->select('entityType')
            ->find();

        $list = [];

        foreach ($groups as $group) {
            $list[] = $group->get('entityType');
        }

        return $list;
    }

    private function processEntityType(string $entityType): void
    {
        if (
            !$this->service->isEnabled($entityType) ||
            !$this->entityManager->hasRepository($entityType)
        ) {
            $deleteQuery = DeleteBuilder::create()
                ->from(StarSubscription::ENTITY_TYPE)
                ->where(['entityType' => $entityType])
                ->build();

            $this->entityManager->getQueryExecutor()->execute($deleteQuery);

            return;
        }

        $stars = $this->entityManager
            ->getRDBRepositoryByClass(StarSubscription::class)
            ->where(['entityType' => $entityType])
            ->sth()
            ->find();

        foreach ($stars as $star) {
            $entityId = $star->get('entityId');
            $userId = $star->get('userId');

            if ($userId === null || $entityId === null) {
                continue;
            }

            $entity = $this->entityManager->getEntityById($entityType, $entityId);
            $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

            if (!$entity || !$user) {
                $this->unstar($userId, $entityType, $entityId);

                continue;
            }

            $aclManager = $this->userAclManagerProvider->get($user);

            if (!$aclManager->checkEntityRead($user, $entity)) {
                $this->unstar($userId, $entityType, $entityId);
            }
        }
    }

    private function unstar(string $userId, string $entityType, string $entityId): void
    {
        $deleteQuery = DeleteBuilder::create()
            ->from(StarSubscription::ENTITY_TYPE)
            ->where([
                'userId' => $userId,
                'entityType' => $entityType,
                'entityId' => $entityId,
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($deleteQuery);
    }
}
