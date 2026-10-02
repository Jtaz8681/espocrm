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

use PDO;
use tests\integration\Core\BaseTestCase;

class QueryTest extends BaseTestCase
{
    public function testQuery1()
    {
        $entityManager = $this->getEntityManager();

        $account = $entityManager->getNewEntity('Account');
        $account->set('name', 'Test');
        $entityManager->saveEntity($account);

        $query = $entityManager->getQueryBuilder()
            ->select()
            ->from('Account')
            ->select('id')
            ->select("CONCAT:(',test',\"+\",'\"', \"',ROUND(1)\")", 'value')
            ->build();

        $rowList = [];

        $sth = $entityManager->getQueryExecutor()->execute($query);

        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $rowList[] = $row;
        }

        $this->assertEquals(",test+\"',ROUND(1)", $rowList[0]['value']);
    }
}
