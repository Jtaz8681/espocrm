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

namespace Espo\ORM;

use Closure;

/**
 * Event dispatcher.
 */
class EventDispatcher
{
    /** @var array{'metadataUpdate': Closure[]} */
    private array $data;

    private const METADATA_UPDATE = 'metadataUpdate';

    public function __construct()
    {
        $this->data = [
            self::METADATA_UPDATE => [],
        ];
    }

    public function subscribeToMetadataUpdate(Closure $callback): void
    {
        $this->data[self::METADATA_UPDATE][] = $callback;
    }

    /**
     * @internal
     * @since 8.4.0
     */
    public function unsubscribeFromMetadataUpdate(Closure $closure): void
    {
        $list = &$this->data[self::METADATA_UPDATE];

        $index = array_search($closure, $list);

        if ($index !== false) {
            unset($list[$index]);

            $list = array_values($list);
        }
    }

    public function dispatchMetadataUpdate(): void
    {
        foreach ($this->data[self::METADATA_UPDATE] as $callback) {
            $callback();
        }
    }
}
