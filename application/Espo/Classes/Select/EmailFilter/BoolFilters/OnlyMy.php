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

namespace Espo\Classes\Select\EmailFilter\BoolFilters;

use Espo\Core\Select\Bool\Filter;
use Espo\Entities\EmailAccount;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class OnlyMy implements Filter
{
    public function __construct(private User $user, private EntityManager $entityManager)
    {}

    public function apply(QueryBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        $part = [];

        $part[] = [
            'parentType' => User::ENTITY_TYPE,
            'parentId' => $this->user->getId(),
        ];

        $idList = [];

        $emailAccountList = $this->entityManager
            ->getRDBRepository(EmailAccount::ENTITY_TYPE)
            ->select(Attribute::ID)
            ->where([
                'assignedUserId' => $this->user->getId(),
            ])
            ->find();

        foreach ($emailAccountList as $emailAccount) {
            $idList[] = $emailAccount->getId();
        }

        if (count($idList)) {
            $part = [
                'OR' => [
                    $part,
                    [
                        'parentType' => EmailAccount::ENTITY_TYPE,
                        'parentId' => $idList,
                    ],
                ]
            ];
        }

        $orGroupBuilder->add(WhereClause::fromRaw($part));
    }
}
