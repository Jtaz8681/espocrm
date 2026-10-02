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

namespace Espo\Modules\Crm\Tools\TargetList;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\HookManager;
use Espo\Core\Name\Field;
use Espo\Core\Record\Collection;
use Espo\Core\Record\Collection as RecordCollection;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Select\SearchParams;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Order;
use Espo\ORM\Query\Select;
use PDO;
use RuntimeException;

class OptOutService
{
    public function __construct(
        private EntityManager $entityManager,
        private MetadataProvider $metadataProvider,
        private EntityProvider $entityProvider,
        private HookManager $hookManager,
        private Acl $acl,
    ) {}

    /**
     * Opt out a target.
     *
     * @throws Forbidden
     * @throws NotFound
     */
    public function optOut(string $id, string $targetType, string $targetId): void
    {
        $targetList = $this->getTargetListForEdit($id);
        $target = $this->entityProvider->get($targetType, $targetId);

        $map = $this->metadataProvider->getEntityTypeLinkMap();

        if (empty($map[$targetType])) {
            throw new Forbidden("Not supported target type.");
        }

        $link = $map[$targetType];

        $this->entityManager
            ->getRelation($targetList, $link)
            ->relateById($target->getId(), ['optedOut' => true]);

        $hookData = [
            'link' => $link,
            'targetId' => $targetId,
            'targetType' => $targetType,
        ];

        $this->hookManager->process(TargetList::ENTITY_TYPE, 'afterOptOut', $targetList, [], $hookData);
    }

    /**
     * Cancel opt-out for a target.
     *
     * @throws Forbidden
     * @throws NotFound
     */
    public function cancelOptOut(string $id, string $targetType, string $targetId): void
    {
        $targetList = $this->getTargetListForEdit($id);
        $target = $this->entityProvider->get($targetType, $targetId);

        $map = $this->metadataProvider->getEntityTypeLinkMap();

        if (empty($map[$targetType])) {
            throw new Forbidden("Not supported target type.");
        }

        $link = $map[$targetType];

        $this->entityManager
            ->getRelation($targetList, $link)
            ->updateColumnsById($target->getId(), ['optedOut' => false]);

        $hookData = [
            'link' => $link,
            'targetId' => $targetId,
            'targetType' => $targetType,
        ];

        $this->hookManager->process('TargetList', TargetList::ENTITY_TYPE, $targetList, [], $hookData);
    }

    /**
     * Find opted out targets in a target list.
     *
     * @return Collection<Entity>
     * @throws Forbidden
     * @throws NotFound
     */
    public function find(string $id, SearchParams $params): Collection
    {
        $this->checkEntity($id);

        $offset = $params->getOffset() ?? 0;
        $maxSize = $params->getMaxSize() ?? 0;

        $em = $this->entityManager;
        $queryBuilder = $em->getQueryBuilder();

        $queryList = [];

        $targetLinkList = $this->metadataProvider->getTargetLinkList();

        foreach ($targetLinkList as $link) {
            $queryList[] = $this->getSelectQueryForLink($id, $link);
        }

        $builder = $queryBuilder
            ->union()
            ->all();

        foreach ($queryList as $query) {
            $builder->query($query);
        }

        $countQuery = $queryBuilder
            ->select()
            ->fromQuery($builder->build(), 'c')
            ->select('COUNT:(c.id)', 'count')
            ->build();

        $row = $em->getQueryExecutor()
            ->execute($countQuery)
            ->fetch(PDO::FETCH_ASSOC);

        $totalCount = $row['count'];

        $unionQuery = $builder
            ->limit($offset, $maxSize)
            ->order(Field::CREATED_AT, 'DESC')
            ->build();

        $sth = $em->getQueryExecutor()->execute($unionQuery);

        $collection = $this->entityManager
            ->getCollectionFactory()
            ->create();

        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $itemEntity = $this->entityManager->getNewEntity($row['entityType']);

            $itemEntity->setMultiple($row);
            $itemEntity->setAsFetched();

            $collection[] = $itemEntity;
        }

        /** @var RecordCollection<Entity> */
        return new RecordCollection($collection, $totalCount);
    }

    private function getSelectQueryForLink(string $id, string $link): Select
    {
        $seed = $this->entityManager->getRDBRepositoryByClass(TargetList::class)->getNew();

        $entityType = $seed->getRelationParam($link, RelationParam::ENTITY);

        if (!$entityType) {
            throw new RuntimeException();
        }

        $linkEntityType = ucfirst(
            $seed->getRelationParam($link, RelationParam::RELATION_NAME) ?? ''
        );

        if ($linkEntityType === '') {
            throw new RuntimeException();
        }

        $key = $seed->getRelationParam($link, RelationParam::MID_KEYS)[1] ?? null;

        if (!$key) {
            throw new RuntimeException();
        }

        return $this->entityManager->getQueryBuilder()
            ->select()
            ->from($entityType)
            ->select([
                'id',
                'name',
                Field::CREATED_AT,
                ["'$entityType'", 'entityType'],
            ])
            ->join(
                $linkEntityType,
                'j',
                [
                    "j.$key:" => 'id',
                    'j.deleted' => false,
                    'j.optedOut' => true,
                    'j.targetListId' => $id,
                ]
            )
            ->order(Field::CREATED_AT, Order::DESC)
            ->build();
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function checkEntity(string $id): void
    {
        $this->entityProvider->getByClass(TargetList::class, $id);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function getTargetListForEdit(string $id): TargetList
    {
        $targetList = $this->entityProvider->getByClass(TargetList::class, $id);

        if (!$this->acl->checkEntityEdit($targetList)) {
            throw new Forbidden("No edit access.");
        }

        return $targetList;
    }
}
