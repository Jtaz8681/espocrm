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

namespace Espo\Core\Record\ActionHistory;

use Espo\Core\Field\LinkParent;
use Espo\Entities\ActionHistoryRecord;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class DefaultActionLogger implements ActionLogger
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user
    ) {}

    /**
     * @inheritDoc
     */
    public function log(string $action, Entity $entity): void
    {
        $historyRecord = $this->entityManager
            ->getRepositoryByClass(ActionHistoryRecord::class)
            ->getNew();

        $historyRecord
            ->setAction($action)
            ->setUserId($this->user->getId())
            ->setAuthTokenId($this->user->get('authTokenId'))
            ->setAuthLogRecordId($this->user->get('authLogRecordId'))
            ->setIpAddress($this->user->get('ipAddress'))
            ->setTarget(LinkParent::fromEntity($entity));

        $this->entityManager->saveEntity($historyRecord);
    }
}
