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

namespace Espo\Modules\Crm\Tools\MassEmail\MessagePreparator;

use Espo\Core\Mail\SenderParams;
use Espo\Modules\Crm\Entities\EmailQueueItem;

class Data
{
    public function __construct(
        private string $id,
        private SenderParams $senderParams,
        private EmailQueueItem $queueItem,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    /** @noinspection PhpUnused */
    public function getSenderParams(): SenderParams
    {
        return $this->senderParams;
    }

    /**
     * @since 9.1.0
     */
    public function getQueueItem(): EmailQueueItem
    {
        return $this->queueItem;
    }
}
