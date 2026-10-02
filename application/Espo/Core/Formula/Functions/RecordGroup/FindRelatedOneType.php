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

namespace Espo\Core\Formula\Functions\RecordGroup;

use Espo\Core\Acl\SystemRestriction;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Select\Primary\Filters\All;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Core\Formula\Functions\RecordGroup\Util\FindQueryUtil;
use Espo\ORM\Type\RelationType;

/**
 * @noinspection PhpUnused
 */
class FindRelatedOneType implements Func
{
    public function __construct(
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private FindQueryUtil $findQueryUtil,
        private SystemRestriction $systemRestriction,
    ) {}

    public function process(EvaluatedArgumentList $arguments): ?string
    {
        if (count($arguments) < 3) {
            throw TooFewArguments::create(3);
        }

        $entityManager = $this->entityManager;

        $entityType = $arguments[0];
        $id = $arguments[1];
        $link = $arguments[2];

        $orderBy = null;
        $order = null;

        if (count($arguments) > 3) {
            $orderBy = $arguments[3];
        }

        if (count($arguments) > 4) {
            $order = $arguments[4];
        }

        if (!$entityType) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($id)) {
            throw BadArgumentType::create(2, 'string');
        }

        if (!is_string($link)) {
            throw BadArgumentType::create(3, 'string');
        }

        if ($orderBy !== null && !is_string($orderBy)) {
            throw BadArgumentType::create(4, 'string|null');
        }

        if ($order !== null && !is_string($order) && !is_bool($order)) {
            throw BadArgumentType::create(5, 'string|bool|null');
        }

        $this->assertLinkRead($entityType, $link);

        $entity = $entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            return null;
        }

        if (!$entity instanceof CoreEntity) {
            throw new Error("Non-core entity.");
        }

        $relationType = $entity->getRelationType($link);

        if (
            in_array($relationType, [
                RelationType::BELONGS_TO,
                RelationType::HAS_ONE,
                RelationType::BELONGS_TO_PARENT,
            ])
        ) {
            $relatedEntity = $entityManager
                ->getRDBRepository($entityType)
                ->getRelation($entity, $link)
                ->select([Attribute::ID])
                ->findOne();

            return $relatedEntity?->getId();
        }

        $foreignEntityType = $entity->getRelationParam($link, RelationParam::ENTITY);

        if (!$foreignEntityType) {
            throw new NotAllowedUsage("Bad or not supported link '$link'.");
        }

        $foreignLink = $entity->getRelationParam($link, RelationParam::FOREIGN);

        if (!$foreignLink) {
            throw new NotAllowedUsage("Not supported link '$link'.");
        }

        $builder = $this->selectBuilderFactory
            ->create()
            ->withPrimaryFilter(All::NAME)
            ->from($foreignEntityType);

        $this->findQueryUtil->applyOrder($builder, $orderBy, $order, 5);

        $whereClause = [];

        if (count($arguments) <= 6) {
            $filter = null;

            if (count($arguments) === 6) {
                $filter = $arguments[5];
            }

            $this->findQueryUtil->applyFilter($builder, $filter, 6);
        } else {
            $i = 5;

            while ($i < count($arguments) - 1) {
                $key = $arguments[$i];
                $value = $arguments[$i + 1];

                $this->findQueryUtil->assertWhereClauseKeyValid($entityType, $key);

                $whereClause[] = [$key => $value];

                $i = $i + 2;
            }
        }

        try {
            $queryBuilder = $builder->buildQueryBuilder();
        } catch (BadRequest|Forbidden $e) {
            throw new NotAllowedUsage($e->getMessage(), 0, $e);
        }

        if (!empty($whereClause)) {
            $queryBuilder->where($whereClause);
        }

        if ($relationType === RelationType::HAS_CHILDREN) {
            $queryBuilder->where([
                $foreignLink . 'Id' => $entity->getId(),
                $foreignLink . 'Type' => $entity->getEntityType(),
            ]);
        } else {
            $queryBuilder
                ->join($foreignLink)
                ->where([
                    $foreignLink . '.' . Attribute::ID => $entity->getId(),
                ]);
        }

        $relatedEntity = $entityManager
            ->getRDBRepository($foreignEntityType)
            ->clone($queryBuilder->build())
            ->select([Attribute::ID])
            ->findOne();

        return $relatedEntity?->getId();
    }

    /**
     * @throws NotAllowedUsage
     */
    private function assertLinkRead(string $entityType, string $link): void
    {
        if (!$this->systemRestriction->checkLinkRead($entityType, $link) ) {
            throw new NotAllowedUsage("Cannot read restricted link $entityType.$link.");
        }
    }
}
