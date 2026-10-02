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

use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\SaveProcessor;
use Espo\Core\ORM\Entity as CoreEntity;

class FieldProcessing
{
    public static int $order = -11;

    private SaveProcessor $saveProcessor;

    public function __construct(SaveProcessor $saveProcessor)
    {
        $this->saveProcessor = $saveProcessor;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function afterSave(Entity $entity, array $options): void
    {
        if (!empty($options['skipFieldProcessing'])) {
            return;
        }

        if (!$entity instanceof CoreEntity) {
            return;
        }

        $this->saveProcessor->process($entity, $options);
    }
}
