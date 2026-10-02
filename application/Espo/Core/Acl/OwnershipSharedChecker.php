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

namespace Espo\Core\Acl;

use Espo\ORM\Entity;
use Espo\Entities\User;

/**
 * @template TEntity of Entity
 */
interface OwnershipSharedChecker extends OwnershipChecker
{
    /**
     * Check whether an entity is shared with a user.
     *
     * @param TEntity $entity
     * @param Table::ACTION_* $action
     * @noinspection PhpDocSignatureInspection
     */
    public function checkShared(User $user, Entity $entity, string $action): bool;
}
