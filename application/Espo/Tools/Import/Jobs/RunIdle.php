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

namespace Espo\Tools\Import\Jobs;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Core\Exceptions\Error;
use Espo\Tools\Import\ImportFactory;
use Espo\Tools\Import\Params as ImportParams;
use Espo\ORM\EntityManager;
use Espo\Entities\User;

class RunIdle implements Job
{
    public function __construct(
        private ImportFactory $factory,
        private EntityManager $entityManager
    ) {}

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function run(Data $data): void
    {
        $raw = $data->getRaw();

        $entityType = $raw->entityType;
        $attachmentId = $raw->attachmentId;
        $importId = $raw->importId;
        $importAttributeList = $raw->importAttributeList;
        $userId = $raw->userId;

        $params = ImportParams::fromRaw($raw->params);

        /** @var ?User $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        if (!$user) {
            throw new Error("Import: User not found.");
        }

        if (!$user->isActive()) {
            throw new Error("Import: User is not active.");
        }

        $this->factory
            ->create()
            ->setEntityType($entityType)
            ->setAttributeList($importAttributeList)
            ->setAttachmentId($attachmentId)
            ->setParams($params)
            ->setId($importId)
            ->setUser($user)
            ->run();
    }
}
