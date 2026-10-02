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

namespace Espo\Core\Formula\Functions\UtilGroup;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Func;
use Espo\Core\Utils\Id\RecordIdGenerator;

class GenerateRecordIdType implements Func
{
    public function __construct(private RecordIdGenerator $recordIdGenerator) {}

    public function process(EvaluatedArgumentList $arguments): string
    {
        return $this->recordIdGenerator->generate();
    }
}
