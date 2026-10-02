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

namespace Espo\Core\Select\Bool\Filters;

use Espo\Core\Name\Field;
use Espo\Core\Select\Bool\Filter;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Entities\User;
use Espo\ORM\Defs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\SelectBuilder;

/**
 * @noinspection PhpUnused
 */
class Shared implements Filter
{
    public const NAME = 'shared';

    public function __construct(
        private string $entityType,
        private User $user,
        private FieldHelper $fieldHelper,
        private Defs $defs
    ) {}

    public function apply(SelectBuilder $queryBuilder, OrGroupBuilder $orGroupBuilder): void
    {
        if (!$this->fieldHelper->hasCollaboratorsField()) {
            return;
        }

        $relationDefs = $this->defs
            ->getEntity($this->entityType)
            ->getRelation(Field::COLLABORATORS);

        $middleEntityType = ucfirst($relationDefs->getRelationshipName());
        $key1 = $relationDefs->getMidKey();
        $key2 = $relationDefs->getForeignMidKey();

        $midConditions = [
            "collaboratorsMiddle.$key1:" => Attribute::ID,
            'collaboratorsMiddle.deleted' => false,
        ];

        foreach ($relationDefs->getConditions() as $key => $value) {
            $midConditions["collaboratorsMiddle.$key"] = $value;
        }

        $subQuery = SelectBuilder::create()
            ->select(Attribute::ID)
            ->from($this->entityType)
            ->leftJoin($middleEntityType, 'collaboratorsMiddle', $midConditions)
            ->where(["collaboratorsMiddle.$key2" => $this->user->getId()])
            ->build();

        $orGroupBuilder->add(
            Cond::in(
                Cond::column('id'),
                $subQuery
            )
        );
    }
}
