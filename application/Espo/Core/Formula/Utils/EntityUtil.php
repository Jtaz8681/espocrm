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

namespace Espo\Core\Formula\Utils;

use Espo\Core\Acl\SystemRestriction;
use Espo\Core\Acl\Exceptions\Restricted;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\ORM\Entity;

/**
 * @internal
 * @since 9.3.0
 */
class EntityUtil
{
    public function __construct(
        private SystemRestriction $systemRestriction,
    ) {}

    /**
     * @throws NotAllowedUsage
     */
    public function assertUpdateAccess(Entity $entity): void
    {
        try {
            $this->systemRestriction->assertUpdate($entity);
        } catch (Restricted $e) {
            throw new NotAllowedUsage($e->getMessage(), previous: $e);
        }
    }

    /**
     * @throws NotAllowedUsage
     */
    public function assertRemoveAccess(Entity $entity): void
    {
        try {
            $this->systemRestriction->assertRemoval($entity);
        } catch (Restricted $e) {
            throw new NotAllowedUsage($e->getMessage(), previous: $e);
        }
    }

    /**
     * @return string[]
     */
    public function getWriteRestrictedAttributeList(string $entityType): array
    {
        return $this->systemRestriction->getWriteRestrictedAttributeList($entityType);
    }
}
