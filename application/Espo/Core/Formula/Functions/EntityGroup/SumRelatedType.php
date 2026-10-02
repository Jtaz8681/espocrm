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

namespace Espo\Core\Formula\Functions\EntityGroup;

use Espo\Core\Acl\SystemRestriction;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Exceptions\NotPassedEntity;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Formula\Functions\RecordGroup\Util\FindQueryUtil;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use PDO;

/**
 * @noinspection PhpUnused
 */
class SumRelatedType implements Func
{
    public function __construct(
        private SystemRestriction $systemRestriction,
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private FindQueryUtil $findQueryUtil,
        private ?Entity $entity = null,
    ) {}

    public function process(EvaluatedArgumentList $arguments): int|float
    {
        $entity = $this->entity ?? throw new NotPassedEntity();

        if (!$entity instanceof CoreEntity) {
            throw new Error("Non-core entity.");
        }

        if (count($arguments) < 2) {
            throw TooFewArguments::create(1);
        }

        $link = $arguments[0];
        $field = $arguments[1];

        if (!is_string($link)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_string($field)) {
            throw BadArgumentType::create(2, 'string');
        }

        $filter = null;

        if (count($arguments) > 2) {
            $filter = $arguments[2];
        }

        $entityType = $entity->getEntityType();

        if (!$this->systemRestriction->checkLinkRead($entityType, $link)) {
            throw new NotAllowedUsage("Cannot read restricted field $entityType.$link.");
        }

        $foreignEntityType = $entity->getRelationParam($link, RelationParam::ENTITY);

        if (!$foreignEntityType) {
            throw new Error("Not supported link '$link'.");
        }

        if (!$this->systemRestriction->checkLinkRead($foreignEntityType, $field)) {
            throw new NotAllowedUsage("Cannot read restricted field $foreignEntityType.$field.");
        }

        $foreignLink = $entity->getRelationParam($link, RelationParam::FOREIGN);
        $foreignLinkAlias = $foreignLink . 'SumRelated';

        if (empty($foreignLink)) {
            throw new Error("No foreign link for link $link.");
        }

        $builder = $this->selectBuilderFactory
            ->create()
            ->from($foreignEntityType);

        if ($filter) {
            $this->findQueryUtil->applyFilter($builder, $filter, 3);
        }

        try {
            $queryBuilder = $builder->buildQueryBuilder();
        } catch (BadRequest|Forbidden $e) {
            throw new Error($e->getMessage(), $e->getCode(), $e);
        }

        $queryBuilder->select([
            [$foreignLinkAlias . '.id', 'foreignId'],
            'SUM:' . $field,
        ]);

        if ($entity->getRelationType($link) === 'hasChildren') {
            $queryBuilder
                ->join(
                    $entity->getEntityType(),
                    $foreignLinkAlias,
                    [
                         $foreignLinkAlias . '.id:' => $foreignLink . 'Id',
                        Attribute::DELETED => false,
                        $foreignLinkAlias . '.id!=' => null,
                    ]
                )
                ->where([
                    $foreignLink . 'Type'  => $entity->getEntityType(),
                ]);
        } else {
            $queryBuilder->join($foreignLink, $foreignLinkAlias);
        }

        $queryBuilder->where([
            $foreignLinkAlias . '.id' => $entity->getId(),
        ]);

        if ($queryBuilder->build()->isDistinct()) {
            // Use a sub-query to weed out duplicate rows.

            $sqQueryBuilder = clone $queryBuilder;

            $sqQueryBuilder
                ->order([])
                ->select([Attribute::ID]);

            $queryBuilder->where([
                'id=s' => $sqQueryBuilder->build(),
            ]);
        }

        $queryBuilder->group($foreignLinkAlias . '.id');

        $sth = $this->entityManager->getQueryExecutor()->execute($queryBuilder->build());

        $rowList = $sth->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rowList)) {
            return 0.0;
        }

        $stringValue = $rowList[0]['SUM:' . $field];

        return floatval($stringValue);
    }
}
