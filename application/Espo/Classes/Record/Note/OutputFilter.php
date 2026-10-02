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

namespace Espo\Classes\Record\Note;

use Espo\Core\Record\Output\Filter;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Tools\Stream\NoteAccessControl;

/**
 * @implements Filter<Note>
 */
class OutputFilter implements Filter
{
    public function __construct(
        private NoteAccessControl $noteAccessControl,
        private User $user,
    ) {}

    public function filter(Entity $entity): void
    {
        $this->noteAccessControl->apply($entity, $this->user);
    }
}
