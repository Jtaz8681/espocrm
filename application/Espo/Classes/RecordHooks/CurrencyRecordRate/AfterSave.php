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

namespace Espo\Classes\RecordHooks\CurrencyRecordRate;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Currency\DatabasePopulator;
use Espo\Core\WebSocket\Submission;
use Espo\Entities\CurrencyRecordRate;
use Espo\ORM\Entity;
use Espo\Tools\Currency\Exceptions\NotEnabled;
use Espo\Tools\Currency\SyncManager;

/**
 * @implements SaveHook<CurrencyRecordRate>
 */
class AfterSave implements SaveHook
{
    public function __construct(
        private SyncManager $syncManager,
        private Submission $submission,
        private DatabasePopulator $databasePopulator,
    ) {}

    public function process(Entity $entity): void
    {
        $code = $entity->getRecord()->getCode();

        try {
            $this->syncManager->updateCode($code);
        } catch (NotEnabled $e) {
            throw new Conflict($e->getMessage(), previous: $e);
        }

        $this->databasePopulator->process();
        $this->submission->submit('appParamsUpdate');
    }
}
