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

namespace Espo\Hooks\Note;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Entities\Note;
use Espo\Tools\Stream\Service;

/**
 * @implements AfterSave<Note>
 */
class StreamUpdatedAt implements AfterSave
{
    public function __construct(private Service $service)
    {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        if (
            $entity->getType() !== Note::TYPE_POST ||
            !$entity->getParentType() ||
            !$this->service->checkIsEnabled($entity->getParentType())
        ) {
            return;
        }

        if (!$entity->getParent()) {
            return;
        }

        $this->service->updateStreamUpdatedAt($entity->getParent());
    }
}
