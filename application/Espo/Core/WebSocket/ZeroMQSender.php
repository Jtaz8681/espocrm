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

namespace Espo\Core\WebSocket;

use Espo\Core\Utils\Config;

use ZMQContext;
use ZMQ;

class ZeroMQSender implements Sender
{
    private const DSN = 'tcp://localhost:5555';

    public function __construct(private Config $config)
    {}

    public function send(string $message): void
    {
        $dsn = $this->config->get('webSocketZeroMQSubmissionDsn') ?? self::DSN;

        $context = new ZMQContext();

        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');

        $socket->connect($dsn);
        $socket->send($message);
        $socket->setSockOpt(ZMQ::SOCKOPT_LINGER, 1000);
        $socket->disconnect($dsn);
    }
}
