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

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Hook\Hook\BeforeRemove;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use Espo\ORM\Exceptions\ValidationException;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\Lock\LockValidationHelper;
use Espo\Tools\Lock\LockMetadataProvider;

/**
 * @noinspection PhpUnused
 */
class ValidateLocked implements BeforeSave, BeforeRemove
{
    public static int $order = 12;

    public function __construct(
        private LockMetadataProvider $lockMetadataProvider,
        private LockValidationHelper $lockValidationHelper,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$this->lockMetadataProvider->isEnabled($entity->getEntityType())) {
            return;
        }

        try {
            $this->lockValidationHelper->validateBeforeSave($entity);
        } catch (Conflict $e) {
            throw new ValidationException(previous: $e);
        }
    }

    public function beforeRemove(Entity $entity, RemoveOptions $options): void
    {
        if (!$this->lockMetadataProvider->isEnabled($entity->getEntityType())) {
            return;
        }

        try {
            $this->lockValidationHelper->validateBeforeRemove($entity);
        } catch (Conflict $e) {
            throw new ValidationException(previous: $e);
        }

        if ($entity->get(Field::IS_LOCKED)) {
            throw new ValidationException("Cannot remove a locked record.");
        }
    }
}
