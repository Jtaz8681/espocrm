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

namespace tests\unit\Espo\Core\Utils\Resource;

use Espo\Core\Utils\Resource\Reader;
use Espo\Core\Utils\Resource\Reader\Params as ReaderParams;
use Espo\Core\Utils\File\Unifier;
use Espo\Core\Utils\File\UnifierObj;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Unifier
     */
    private $unifier;

    /**
     * @var UnifierObj
     */
    private $unifierObj;

    protected function setUp(): void
    {
        $this->unifier = $this->createMock(Unifier::class);
        $this->unifierObj = $this->createMock(UnifierObj::class);

        $this->reader = new Reader($this->unifier, $this->unifierObj);
    }

    public function testRead1(): void
    {
        $params = ReaderParams::create();

        $this->unifierObj
            ->expects($this->once())
            ->method('unify')
            ->with('test/hello', false)
            ->willReturn((object) []);

        $this->reader->read('test/hello', $params);
    }

    public function testRead2(): void
    {
        $params = ReaderParams::create();

        $this->unifier
            ->expects($this->once())
            ->method('unify')
            ->with('test/hello', false)
            ->willReturn([]);

        $this->reader->readAsArray('test/hello', $params);
    }

    public function testRead3(): void
    {
        $params = ReaderParams::create()
            ->withNoCustom();

        $this->unifier
            ->expects($this->once())
            ->method('unify')
            ->with('test/hello', true)
            ->willReturn([]);

        $this->reader->readAsArray('test/hello', $params);
    }
}
