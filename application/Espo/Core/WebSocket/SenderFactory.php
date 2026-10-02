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

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Core\Binding\Factory;

use RuntimeException;

/**
 * @implements Factory<Sender>
 */
class SenderFactory implements Factory
{
    private const DEFAULT_MESSAGER = 'ZeroMQ';

    public function __construct(
        private InjectableFactory $injectableFactory,
        private ConfigDataProvider $config,
        private Metadata $metadata,
    ) {}

    public function create(): Sender
    {
        $messager = $this->config->getMessager() ?? self::DEFAULT_MESSAGER;

        /** @var ?class-string<Sender> $className */
        $className = $this->metadata->get(['app', 'webSocket', 'messagers', $messager, 'senderClassName']);

        if (!$className) {
            throw new RuntimeException("No sender for messager '$messager'.");
        }

        return $this->injectableFactory->create($className);
    }
}
