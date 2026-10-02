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

namespace Espo\Classes\Record\Common\Lock;

use Espo\Core\Record\DeleteParams;
use Espo\Core\Record\Hook\DeleteHook;
use Espo\ORM\Entity;
use Espo\Tools\Lock\LockValidationHelper;

/**
 * @noinspection PhpUnused
 */
class BeforeDeleteValidation implements DeleteHook
{
    public function __construct(
        private LockValidationHelper $lockValidationHelper,
    ) {}

    public function process(Entity $entity, DeleteParams $params): void
    {
        $this->lockValidationHelper->validateBeforeRemove($entity);
    }
}
