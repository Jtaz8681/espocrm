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

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\NextNumber\Processor as Processor;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<CoreEntity>
 */
class NextNumber implements BeforeSave
{
    public function __construct(private Processor $processor)
    {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $this->processor->process($entity, $options);
    }
}
