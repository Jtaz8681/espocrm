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

namespace Espo\Modules\Crm\Tools\MassEmail;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Mail\Exceptions\NoSmtp;
use Espo\Entities\InboundEmail;
use Espo\Modules\Crm\Entities\MassEmail as MassEmailEntity;
use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use stdClass;

class Service
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private QueueCreator $queueCreator,
        private SendingProcessor $sendingProcessor
    ) {}

    /**
     * SMTP data for the front-end.
     *
     * @return stdClass[]
     * @throws Forbidden
     */
    public function getSmtpAccountDataList(): array
    {
        if (
            !$this->acl->checkScope(MassEmailEntity::ENTITY_TYPE, Table::ACTION_CREATE) &&
            !$this->acl->checkScope(MassEmailEntity::ENTITY_TYPE, Table::ACTION_EDIT)
        ) {
            throw new Forbidden();
        }

        $dataList = [];

        /** @var Collection<InboundEmail> $inboundEmailList */
        $inboundEmailList = $this->entityManager
            ->getRDBRepository(InboundEmail::ENTITY_TYPE)
            ->where([
                'useSmtp' => true,
                'status' => InboundEmail::STATUS_ACTIVE,
                'smtpIsForMassEmail' => true,
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

    /**
     * Send test.
     *
     * @param stdClass[] $targetDataList
     * @throws BadRequest
     * @throws Error
     * @throws Forbidden
     * @throws NotFound
     * @throws NoSmtp
     */
    public function processTest(string $id, array $targetDataList): void
    {
        $targetList = [];

        if (count($targetDataList) === 0) {
            throw new BadRequest("Empty target list.");
        }

        foreach ($targetDataList as $item) {
            if (empty($item->id) || empty($item->type)) {
                throw new BadRequest();
            }

            $targetId = $item->id;
            $targetType = $item->type;

            $target = $this->entityManager->getEntityById($targetType, $targetId);

            if (!$target) {
                throw new Error("Target not found.");
            }

            if (!$this->acl->check($target, Table::ACTION_READ)) {
                throw new Forbidden();
            }

            $targetList[] = $target;
        }

        /** @var ?MassEmailEntity $massEmail */
        $massEmail = $this->entityManager->getEntityById(MassEmailEntity::ENTITY_TYPE, $id);

        if (!$massEmail) {
            throw new NotFound();
        }

        if (!$this->acl->check($massEmail, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $this->createTestQueue($massEmail, $targetList);
        $this->processTestSending($massEmail);
    }

    /**
     * @param iterable<Entity> $targetList
     * @throws Error
     */
    private function createTestQueue(MassEmailEntity $massEmail, iterable $targetList): void
    {
        $this->queueCreator->create($massEmail, true, $targetList);
    }

    /**
     * @throws Error
     * @throws NoSmtp
     */
    private function processTestSending(MassEmailEntity $massEmail): void
    {
        $this->sendingProcessor->process($massEmail, true);
    }
}
