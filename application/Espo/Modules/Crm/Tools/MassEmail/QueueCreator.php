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

use Espo\Core\Name\Field;
use Espo\Core\Utils\Log;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;
use Espo\Repositories\EmailAddress as EmailAddressRepository;
use Espo\Entities\EmailAddress;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\Modules\Crm\Entities\EmailQueueItem;
use Espo\Core\Exceptions\Error;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Metadata;
use stdClass;

class QueueCreator
{
    private const ERASED_PREFIX = 'ERASED:';

    /** @var string[] */
    protected array $targetLinkList;

    public function __construct(
        protected EntityManager $entityManager,
        private Metadata $metadata,
        private Log $log,
    ) {
        $this->targetLinkList = $this->metadata->get(['scopes', 'TargetList', 'targetLinkList']) ?? [];
    }

    private function cleanupQueueItems(MassEmail $massEmail): void
    {
        $delete = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(EmailQueueItem::ENTITY_TYPE)
            ->where([
                 'massEmailId' => $massEmail->getId(),
                 'status' => [
                     EmailQueueItem::STATUS_PENDING,
                     EmailQueueItem::STATUS_FAILED,
                ],
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($delete);
    }

    /**
     * @param iterable<Entity> $additionalTargetList
     * @throws Error
     */
    public function create(MassEmail $massEmail, bool $isTest = false, iterable $additionalTargetList = []): void
    {
        if (!$isTest && $massEmail->getStatus() !== MassEmail::STATUS_PENDING) {
            throw new Error("Mass Email {$massEmail->getId()} should has status 'Pending'.");
        }

        if ($this->toSkipAsInactive($massEmail, $isTest)) {
            $this->log->notice("Skipping mass email {id} queue creation for inactive campaign.", [
                'id' => $massEmail->getId(),
            ]);

            return;
        }

        $withOptedOut = $massEmail->getCampaign()?->getType() === Campaign::TYPE_INFORMATIONAL_EMAIL;

        if (!$isTest) {
            $this->cleanupQueueItems($massEmail);
        }

        $itemList = [];

        if (!$isTest) {
            $itemList = $this->getItemList($massEmail, $withOptedOut);
        }

        foreach ($additionalTargetList as $record) {
            $item = $record->getValueMap();

            $item->entityType = $record->getEntityType();

            $itemList[] = $item;
        }

        foreach ($itemList as $item) {
            $emailAddress = $item->emailAddress ?? null;

            if (!$emailAddress) {
                continue;
            }

            if (str_starts_with($emailAddress, self::ERASED_PREFIX)) {
                continue;
            }

            $emailAddressRecord = $this->getEmailAddressRepository()->getByAddress($emailAddress);

            if ($emailAddressRecord && !$withOptedOut && $emailAddressRecord->isOptedOut()) {
                continue;
            }

            if ($emailAddressRecord && $emailAddressRecord->isInvalid()) {
                continue;
            }

            $queueItem = $this->entityManager->getNewEntity(EmailQueueItem::ENTITY_TYPE);

            $queueItem->set([
                'massEmailId' => $massEmail->getId(),
                'status' => EmailQueueItem::STATUS_PENDING,
                'targetId' => $item->id,
                'targetType' => $item->entityType,
                'isTest' => $isTest,
            ]);

            $this->entityManager->saveEntity($queueItem);
        }

        if ($isTest) {
            return;
        }

        $massEmail->setStatus(MassEmail::STATUS_IN_PROCESS);

        if ($itemList === []) {
            $massEmail->setStatus(MassEmail::STATUS_COMPLETE);
        }

        $this->entityManager->saveEntity($massEmail);
    }

    private function getEmailAddressRepository(): EmailAddressRepository
    {
        /** @var EmailAddressRepository */
        return $this->entityManager->getRepository(EmailAddress::ENTITY_TYPE);
    }

    private function toSkipAsInactive(MassEmail $massEmail, bool $isTest): bool
    {
        return !$isTest &&
            $massEmail->getCampaign() &&
            $massEmail->getCampaign()->getStatus() === Campaign::STATUS_INACTIVE;
    }

    /**
     * @return stdClass[]
     */
    private function getItemList(MassEmail $massEmail, bool $withOptedOut): array
    {
        $metTargetHash = [];
        $metEmailAddressHash = [];

        $itemList = [];

        foreach ($massEmail->getExcludingTargetLists() as $excludingTargetList) {
            foreach ($this->targetLinkList as $link) {
                $targets = $this->entityManager
                    ->getRelation($excludingTargetList, $link)
                    ->sth()
                    ->select([Attribute::ID, Field::EMAIL_ADDRESS])
                    ->find();

                foreach ($targets as $target) {
                    $hashId = $target->getEntityType() . '-' . $target->getId();

                    $metTargetHash[$hashId] = true;

                    $emailAddress = $target->get(Field::EMAIL_ADDRESS);

                    if ($emailAddress) {
                        $metEmailAddressHash[$emailAddress] = true;
                    }
                }
            }
        }

        foreach ($massEmail->getTargetLists() as $targetList) {
            foreach ($this->targetLinkList as $link) {
                $where = [];

                if (!$withOptedOut) {
                    $where = ['@relation.optedOut' => false];
                }

                $records = $this->entityManager
                    ->getRelation($targetList, $link)
                    ->select([Attribute::ID, Field::EMAIL_ADDRESS])
                    ->sth()
                    ->where($where)
                    ->find();

                foreach ($records as $record) {
                    $hashId = $record->getEntityType() . '-' . $record->getId();

                    $emailAddress = $record->get(Field::EMAIL_ADDRESS);

                    if (!$emailAddress) {
                        continue;
                    }

                    if (!empty($metEmailAddressHash[$emailAddress])) {
                        continue;
                    }

                    if (!empty($metTargetHash[$hashId])) {
                        continue;
                    }

                    $item = $record->getValueMap();

                    $item->entityType = $record->getEntityType();

                    $itemList[] = $item;

                    $metTargetHash[$hashId] = true;
                    $metEmailAddressHash[$emailAddress] = true;
                }
            }
        }

        return $itemList;
    }
}
