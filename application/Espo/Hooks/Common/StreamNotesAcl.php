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

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Entity;
use Espo\Tools\Stream\NoteAcl\AccessModifier;

/**
 * Notes having `related` or `superParent` are subjects to access control
 * through `users` and `teams` fields.
 *
 * When users or teams of `related` or `parent` record are changed
 * the note record will be changed too.
 *
 * @noinspection PhpUnused
 */
class StreamNotesAcl
{
    public static int $order = 10;

    public function __construct(private AccessModifier $processor)
    {}

    /**
     * @param array<string, mixed> $options
     */
    public function afterSave(Entity $entity, array $options): void
    {
        if (!empty($options[SaveOption::NO_STREAM])) {
            return;
        }

        if (!empty($options[SaveOption::SILENT])) {
            return;
        }

        if (!empty($options['skipStreamNotesAcl'])) {
            return;
        }

        if ($entity->isNew()) {
            return;
        }

        $this->processor->process($entity);
    }
}
