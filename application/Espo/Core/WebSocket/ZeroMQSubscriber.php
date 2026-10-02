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

use React\EventLoop\LoopInterface;
use React\ZMQ\Context as ZMQContext;
use Evenement\EventEmitter;
use React\ZMQ\SocketWrapper;

use ZMQ;

class ZeroMQSubscriber implements Subscriber
{
    private const DSN = 'tcp://127.0.0.1:5555';

    public function __construct(private Config $config)
    {}

    public function subscribe(Pusher $pusher, LoopInterface $loop): void
    {
        $dsn = $this->config->get('webSocketZeroMQSubscriberDsn') ?? self::DSN;

        $context = new ZMQContext($loop);

        /** @var EventEmitter $pull */
        /** @var SocketWrapper $pull */
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);

        $pull->bind($dsn);
        $pull->on('message', [$pusher, 'onMessageReceive']);
    }
}
