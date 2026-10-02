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

namespace Espo\Classes\RecordHooks\LeadCapture;

use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\LeadCapture;
use Espo\ORM\Entity;
use Espo\Tools\LeadCapture\Service;

/**
 * @noinspection PhpUnused
 * @implements SaveHook<LeadCapture>
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private Service $service
    ) {}

    public function process(Entity $entity): void
    {
        $entity->setApiKey($this->service->generateApiKey());
        $entity->setFormId($this->service->generateFormId());
    }
}
