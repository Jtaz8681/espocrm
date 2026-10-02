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

namespace tests\unit\Espo\Tools\Export;

use Espo\Tools\Export\Params;
use PHPUnit\Framework\TestCase;

class ParamsTest extends TestCase
{
    public function testSerialize(): void
    {
        $params = new Params('Test');

        $params = Params::fromSerializedRaw(base64_encode(serialize($params)));

        $this->assertEquals('Test', $params->getEntityType());
    }
}
