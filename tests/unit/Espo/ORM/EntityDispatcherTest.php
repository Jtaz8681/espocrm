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

namespace tests\unit\Espo\ORM;

use Espo\ORM\EventDispatcher;
use PHPUnit\Framework\TestCase;

class EntityDispatcherTest extends TestCase
{
    public function testSubscribe(): void
    {
        $eventDispatcher = new EventDispatcher();

        $dispatched = false;

        $eventDispatcher->subscribeToMetadataUpdate(function () use (&$dispatched) {
            $dispatched = true;
        });

        $eventDispatcher->dispatchMetadataUpdate();

        $this->assertTrue($dispatched);
    }

    public function testUnsubscribe(): void
    {
        $eventDispatcher = new EventDispatcher();

        $dispatched1 = false;

        $closure1 = function () use (&$dispatched1) {
            $dispatched1 = true;
        };

        $dispatched2 = false;

        $closure2 = function () use (&$dispatched2) {
            $dispatched2 = true;
        };

        $eventDispatcher->subscribeToMetadataUpdate($closure1);
        $eventDispatcher->subscribeToMetadataUpdate($closure2);
        $eventDispatcher->unsubscribeFromMetadataUpdate($closure1);

        $eventDispatcher->dispatchMetadataUpdate();

        $this->assertFalse($dispatched1);
        $this->assertTrue($dispatched2);
    }
}
