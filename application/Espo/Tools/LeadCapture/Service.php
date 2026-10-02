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

namespace Espo\Tools\LeadCapture;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\Util;
use Espo\Entities\InboundEmail;
use Espo\Entities\LeadCapture as LeadCaptureEntity;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use stdClass;

class Service
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceContainer $recordServiceContainer,
        private User $user
    ) {}

    public function isApiKeyValid(string $apiKey): bool
    {
        $leadCapture = $this->entityManager
            ->getRDBRepositoryByClass(LeadCaptureEntity::class)
            ->where([
                'apiKey' => $apiKey,
                'isActive' => true,
            ])
            ->findOne();

        if ($leadCapture) {
            return true;
        }

        return false;
    }

    /**
     * @throws ForbiddenSilent
     * @throws NotFound
     * @throws Forbidden
     */
    public function generateNewApiKeyForEntity(string $id): LeadCaptureEntity
    {
        $service = $this->recordServiceContainer->getByClass(LeadCaptureEntity::class);

        $entity = $service->getEntity($id);

        if (!$entity) {
            throw new NotFound();
        }

        $entity->setApiKey($this->generateApiKey());

        $this->entityManager->saveEntity($entity);

        $service->loadAdditionalFields($entity);
        $service->prepareEntityForOutput($entity);

        return $entity;
    }

    /**
     * @throws ForbiddenSilent
     * @throws NotFound
     * @throws Forbidden
     */
    public function generateNewFormIdForEntity(string $id): LeadCaptureEntity
    {
        $service = $this->recordServiceContainer->getByClass(LeadCaptureEntity::class);

        $entity = $service->getEntity($id);

        if (!$entity) {
            throw new NotFound();
        }

        $entity->setFormId($this->generateFormId());

        $this->entityManager->saveEntity($entity);

        $service->loadAdditionalFields($entity);
        $service->prepareEntityForOutput($entity);

        return $entity;
    }

    public function generateApiKey(): string
    {
        return Util::generateApiKey();
    }

    public function generateFormId(): string
    {
        return Util::generateId();
    }

    /**
     * @return stdClass[]
     * @throws Forbidden
     */
    public function getSmtpAccountDataList(): array
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $dataList = [];

        $inboundEmailList = $this->entityManager
            ->getRDBRepositoryByClass(InboundEmail::class)
            ->where([
                'useSmtp' => true,
                'status' => InboundEmail::STATUS_ACTIVE,
                ['emailAddress!=' => ''],
                ['emailAddress!=' => null],
            ])
            ->find();

        foreach ($inboundEmailList as $inboundEmail) {
            $item = (object) [];

            $key = 'inboundEmail:' . $inboundEmail->getId();

            $item->key = $key;
            $item->emailAddress = $inboundEmail->getEmailAddress();
            $item->fromName = $inboundEmail->getFromName();

            $dataList[] = $item;
        }

        return $dataList;
    }
}
