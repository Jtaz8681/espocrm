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

namespace Espo\Core\Mail\Account\Fetcher;

use Espo\Core\Mail\Account\Storage;
use Espo\Core\Mail\Exceptions\ImapError;
use Espo\Core\Mail\Message;
use Espo\Core\Mail\MessageWrapper;
use Espo\Core\Mail\Parser;

/**
 * @internal
 */
class MessageFactory
{
    public function __construct() {}

    /**
     * @throws ImapError
     */
    public function create(
        int $id,
        Storage $storage,
        Parser $parser,
        bool $peek,
    ): Message {

        return new MessageWrapper(
            id: $id,
            storage: $storage,
            parser: $parser,
            peek: $peek,
        );
    }
}
