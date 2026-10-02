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

namespace Espo\Modules\Crm\Classes\RecordHooks\TargetList;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Entities\CampaignLogRecord;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\Modules\Crm\Tools\TargetList\MetadataProvider;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @implements SaveHook<TargetList>
 */
class AfterCreate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private MetadataProvider $metadataProvider
    ) {}

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function process(Entity $entity): void
    {
        if (
            !$entity->get('sourceCampaignId') ||
            !$entity->get('includingActionList')
        ) {
            return;
        }

        /** @var string $campaignId */
        $campaignId = $entity->get('sourceCampaignId');
        /** @var string[] $includingActionList */
        $includingActionList = $entity->get('includingActionList');
        /** @var string[] $excludingActionList */
        $excludingActionList = $entity->get('excludingActionList') ?? [];

        $this->populateFromCampaignLog(
            $entity,
            $campaignId,
            $includingActionList,
            $excludingActionList
        );
    }

    /**
     * @param string[] $includingActionList
     * @param string[] $excludingActionList
     * @throws NotFound
     * @throws Forbidden
     */
    protected function populateFromCampaignLog(
        TargetList $entity,
        string $sourceCampaignId,
        array $includingActionList,
        array $excludingActionList
    ): void {

        $campaign = $this->entityManager->getEntityById(Campaign::ENTITY_TYPE, $sourceCampaignId);

        if (!$campaign) {
            throw new NotFound("Campaign not found.");
        }

        if (!$this->acl->check($campaign, Table::ACTION_READ)) {
            throw new Forbidden("No access to campaign.");
        }

        $queryBuilder = $this->entityManager
            ->getQueryBuilder()
            ->select()
            ->from(CampaignLogRecord::ENTITY_TYPE)
            ->select([Attribute::ID, 'parentId', 'parentType'])
            ->where([
                'isTest' => false,
                'campaignId' => $sourceCampaignId,
            ]);

        $notQueryBuilder = clone $queryBuilder;

        $queryBuilder->where([
            'action=' => $includingActionList,
        ]);

        $queryBuilder->group([
            'parentId',
            'parentType',
            Attribute::ID,
        ]);

        $notQueryBuilder->where(['action=' => $excludingActionList]);
        $notQueryBuilder->select([Attribute::ID]);

        /** @var iterable<CampaignLogRecord> $logRecords */
        $logRecords = $this->entityManager
            ->getRDBRepository(CampaignLogRecord::ENTITY_TYPE)
            ->clone($queryBuilder->build())
            ->find();

        $entityTypeLinkMap = $this->metadataProvider->getEntityTypeLinkMap();

        foreach ($logRecords as $logRecord) {
            if (!$logRecord->getParent()) {
                continue;
            }

            $parentType = $logRecord->getParent()->getEntityType();
            $parentId = $logRecord->getParent()->getId();

            if (!$parentType) {
                continue;
            }

            if (empty($entityTypeLinkMap[$parentType])) {
                continue;
            }

            $existing = null;

            if (!empty($excludingActionList)) {
                $cloneQueryBuilder = clone $notQueryBuilder;

                $cloneQueryBuilder->where([
                    'parentType' => $parentType,
                    'parentId' => $parentId,
                ]);

                $existing = $this->entityManager
                    ->getRDBRepository(CampaignLogRecord::ENTITY_TYPE)
                    ->clone($cloneQueryBuilder->build())
                    ->findOne();
            }

            if ($existing) {
                continue;
            }

            $relation = $entityTypeLinkMap[$parentType];

            $this->entityManager
                ->getRDBRepositoryByClass(TargetList::class)
                ->getRelation($entity, $relation)
                ->relateById($parentId);
        }
    }
}
