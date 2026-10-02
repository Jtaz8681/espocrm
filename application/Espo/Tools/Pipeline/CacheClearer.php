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

namespace Espo\Tools\Pipeline;

use Espo\Core\Utils\DataCache;
use Espo\Core\WebSocket\Submission;

class CacheClearer
{
    private const string CACHE_KEY = 'pipelines';

    public function __construct(
        private DataCache $dataCache,
        private Submission $submission,
    ) {}

    public function clear(): void
    {
        $this->dataCache->clear(self::CACHE_KEY);
        $this->submission->submit('appParamsUpdate');
    }
}
