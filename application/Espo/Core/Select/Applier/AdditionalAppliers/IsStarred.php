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

namespace Espo\Core\Select\Applier\AdditionalAppliers;

use Espo\Core\Name\Field;
use Espo\Core\Select\Applier\AdditionalApplier;
use Espo\Core\Select\SearchParams;
use Espo\Core\Utils\Metadata;
use Espo\Entities\StarSubscription;
use Espo\Entities\User;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\SelectBuilder;

/**
 * @noinspection PhpUnused
 */
class IsStarred implements AdditionalApplier
{
    public function __construct(
        private string $entityType,
        private User $user,
        private Metadata $metadata,
    ) {}

    public function apply(SelectBuilder $queryBuilder, SearchParams $searchParams): void
    {
        if (!$this->metadata->get("scopes.$this->entityType.stars")) {
            return;
        }

        $queryBuilder
            ->select(Expr::isNotNull(Expr::column('starSubscription.id')), Field::IS_STARRED)
            ->leftJoin(StarSubscription::ENTITY_TYPE, 'starSubscription', [
                'userId' => $this->user->getId(),
                'entityType' => $this->entityType,
                'entityId:' => 'id',
            ]);
    }
}
