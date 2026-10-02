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

namespace Espo\Core\Select\AccessControl\Filters;

use Espo\Core\Acl\AssignmentChecker\Helper;
use Espo\Core\Name\Field;
use Espo\Core\Select\AccessControl\Filter;
use Espo\ORM\Defs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\SelectBuilder;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;

use LogicException;

class ForeignOnlyOwn implements Filter
{
    public function __construct(
        private string $entityType,
        private User $user,
        private Metadata $metadata,
        private Defs $defs,
        private Helper $helper,
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $link = $this->metadata->get(['aclDefs', $this->entityType, 'link']);

        if (!$link) {
            throw new LogicException("No `link` in aclDefs for $this->entityType.");
        }

        $alias = $link . 'Access';

        $queryBuilder->leftJoin($link, $alias);

        $foreignEntityType = $this->defs
            ->getEntity($this->entityType)
            ->getRelation($link)
            ->getForeignEntityType();

        $foreignEntityDefs = $this->defs->getEntity($foreignEntityType);

        $orBuilder = OrGroup::createBuilder();

        if ($this->helper->hasCollaboratorsField($foreignEntityType)) {
            $orBuilder->add(
                Cond::equal(
                    Expr::column("$alias." . Attribute::ID),
                    SelectBuilder::create()
                        ->from(User::RELATIONSHIP_ENTITY_COLLABORATOR, 's')
                        ->select('s.entityId')
                        ->where(
                            Cond::and(
                                Cond::equal(
                                    Expr::column('s.entityType'),
                                    $foreignEntityType,
                                ),
                                Cond::equal(
                                    Expr::column('s.userId'),
                                    $this->user->getId(),
                                )
                            )
                        )
                        ->build()
                )
            );
        }

        if ($this->helper->hasAssignedUsersField($foreignEntityType)) {
            $orBuilder->add(
                Cond::equal(
                    Expr::column("$alias." . Attribute::ID),
                    SelectBuilder::create()
                        ->from(User::RELATIONSHIP_ENTITY_USER, 's')
                        ->select('s.entityId')
                        ->where(
                            Cond::and(
                                Cond::equal(
                                    Expr::column('s.entityType'),
                                    $foreignEntityType,
                                ),
                                Cond::equal(
                                    Expr::column('s.userId'),
                                    $this->user->getId(),
                                )
                            )
                        )
                        ->build()
                )
            );

            $queryBuilder->where($orBuilder->build());

            return;
        }

        if ($foreignEntityDefs->hasField(Field::ASSIGNED_USER)) {
            $orBuilder->add(
                Cond::equal(
                    Expr::column("$alias.assignedUserId"),
                    $this->user->getId()
                )
            );

            $queryBuilder->where($orBuilder->build());

            return;
        }

        if ($foreignEntityDefs->hasField(Field::CREATED_BY)) {
            $orBuilder->add(
                Cond::equal(
                    Expr::column("$alias.createdById"),
                    $this->user->getId()
                )
            );

            $queryBuilder->where($orBuilder->build());

            return;
        }

        $queryBuilder->where([Attribute::ID => null]);
    }
}
