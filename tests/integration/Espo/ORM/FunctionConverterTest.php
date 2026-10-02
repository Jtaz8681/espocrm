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

namespace tests\integration\Espo\ORM;

use Espo\ORM\Query\Part\Selection;
use tests\integration\Core\BaseTestCase;

class FunctionConverterTest extends BaseTestCase
{
    public function testAbs(): void
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->getQueryBuilder()
            ->select(Selection::fromString('ABS:(-1)')->withAlias('value'))
            ->build();

        $sth = $entityManager->getQueryExecutor()->execute($query);

        $value = $sth->fetch()['value'];

        $this->assertEquals('1', $value);
    }
}
