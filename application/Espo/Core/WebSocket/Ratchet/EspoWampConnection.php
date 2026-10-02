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

namespace Espo\Core\WebSocket\Ratchet;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use stdClass;

class EspoWampConnection extends WampConnection
{
    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(ConnectionInterface $conn)
    {
        $this->wrappedConn = $conn;

        $this->WAMP = new stdClass;
        $this->WAMP->sessionId = str_replace('.', '', uniqid((string) mt_rand(), true));
        $this->WAMP->prefixes = [];
    }
}
