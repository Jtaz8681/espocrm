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

namespace Espo\Core\Log;

use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;

use Espo\Core\InjectableFactory;

class HandlerListLoader
{
    public function __construct(
        private readonly InjectableFactory $injectableFactory,
        private readonly DefaultHandlerLoader $defaultLoader
    ) {}

    /**
     * @param array<array<string, mixed>> $dataList
     * @return HandlerInterface[]
     */
    public function load(array $dataList, ?string $defaultLevel = null): array
    {
        $handlerList = [];

        foreach ($dataList as $item) {
            $handler = $this->loadHandler($item, $defaultLevel);

            $handlerList[] = $handler;
        }

        return $handlerList;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function loadHandler(array $data, ?string $defaultLevel = null): HandlerInterface
    {
        $params = $data['params'] ?? [];
        $params['level'] ??= $defaultLevel;

        /** @var ?class-string<HandlerLoader> $loaderClassName */
        $loaderClassName = $data['loaderClassName'] ?? null;

        if ($loaderClassName) {
            $loader = $this->injectableFactory->create($loaderClassName);

            $handler = $loader->load($params);

            if ($handler instanceof FormattableHandlerInterface) {
                $formatter = $this->defaultLoader->loadFormatter($data);

                if ($formatter) {
                    $handler->setFormatter($formatter);
                }
            }

            return $handler;
        }

        return $this->defaultLoader->load($data, $defaultLevel);
    }
}
