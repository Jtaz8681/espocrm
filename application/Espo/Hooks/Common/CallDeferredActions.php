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

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Repository\Option\SaveContext;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Entity>
 */
class CallDeferredActions implements AfterSave
{
    public static int $order = 101;

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $saveContext = SaveContext::obtainFromOptions($options);

        if (!$saveContext) {
            return;
        }

        $saveContext->callDeferredActions();
    }
}
