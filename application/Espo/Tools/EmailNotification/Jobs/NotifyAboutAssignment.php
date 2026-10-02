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

namespace Espo\Tools\EmailNotification\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;

use Espo\Tools\EmailNotification\AssignmentProcessor;
use Espo\Tools\EmailNotification\AssignmentProcessorData;

class NotifyAboutAssignment implements Job
{
    public function __construct(private AssignmentProcessor $assignmentProcessor)
    {}

    public function run(Data $data): void
    {
        $this->assignmentProcessor->process(
            AssignmentProcessorData::create()
                ->withAssignerUserId($data->get('assignerUserId'))
                ->withEntityId($data->get('entityId'))
                ->withEntityType($data->get('entityType'))
                ->withUserId($data->get('userId'))
        );
    }
}
