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

namespace Espo\Tools\Stream\NoteAcl;

use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;

/**
 * Changes users and teams of notes related to an entity according users and teams of the entity.
 *
 * Notes having `related` or `superParent` are subjects to access control
 * through `users` and `teams` fields.
 *
 * When users or teams of `related` or `parent` record are changed
 * the note record will be changed too.
 *
 * @internal
 * @todo Job to process the rest, after the last ID.
 */
class AccessModifier
{
    /** @var string[] */
    private array $ignoreEntityTypeList = [
        'Note',
        'User',
        'Team',
        'Role',
        'Portal',
        'PortalRole',
    ];

    public function __construct(
        private Metadata $metadata,
        private Processor $processor
    ) {}

    /**
     * @internal
     */
    public function process(Entity $entity): void
    {
        if (!$entity instanceof CoreEntity) {
            return;
        }

        if (!$this->toProcess($entity)) {
            return;
        }

        $this->processor->process($entity);
    }

    private function toProcess(CoreEntity $entity): bool
    {
        $entityType = $entity->getEntityType();

        if (in_array($entityType, $this->ignoreEntityTypeList)) {
            return false;
        }

        if (!$this->metadata->get(['scopes', $entityType, 'acl'])) {
            return false;
        }

        if (!$this->metadata->get(['scopes', $entityType, 'object'])) {
            return false;
        }

        return true;
    }
}
