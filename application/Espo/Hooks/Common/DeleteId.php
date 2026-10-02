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

namespace Espo\Hooks\Common;

use Espo\Core\Hook\Hook\BeforeRemove;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Core\Hook\Hook\BeforeSave;

/**
 * Handles 'deleteId' on soft-deletes.
 *
 * @implements BeforeSave<Entity>
 * @implements BeforeRemove<Entity>
 */
class DeleteId implements BeforeSave, BeforeRemove
{
    private const ID_ATTR = 'deleteId';
    private const DELETED_ATTR = Attribute::DELETED;

    public function __construct(
        private Metadata $metadata,
    ) {}

    public function beforeRemove(Entity $entity, RemoveOptions $options): void
    {
        if (!$this->hasDeleteId($entity)) {
            return;
        }

        $entity->set(self::ID_ATTR, Util::generateId());
    }

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$this->hasDeleteId($entity)) {
            return;
        }

        if (!$entity->isAttributeChanged(self::DELETED_ATTR)) {
            return;
        }

        $deleteId = $entity->get(self::DELETED_ATTR) ? Util::generateId() : '0';

        $entity->set(self::ID_ATTR, $deleteId);
    }

    private function hasDeleteId(Entity $entity): bool
    {
        return $entity->hasAttribute(self::DELETED_ATTR) &&
            $this->metadata->get("entityDefs.{$entity->getEntityType()}.deleteId");
    }
}
