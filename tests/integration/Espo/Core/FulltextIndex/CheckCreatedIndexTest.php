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

namespace tests\integration\Espo\Core\FulltextIndex;

use Espo\Core\Utils\Util;
use PHPUnit\Framework\Attributes\DataProvider;
use tests\integration\Core\BaseTestCase;

class CheckCreatedIndexTest extends BaseTestCase
{
    protected ?string $dataFile = 'InitData.php';
    protected ?string $pathToFiles = 'Core/FulltextIndex/customFiles';

    static public function entityList()
    {
        return [
            ['Email'],
            ['Account'],
            ['Contact'],
        ];
    }

    #[DataProvider('entityList')]
    public function testCreatedIndexes($entityName)
    {
        $entityManager = $this->getContainer()->get('entityManager');
        $pdo = $entityManager->getPDO();

        $fulltextFieldList = $entityManager->getMetadata()->get($entityName, 'fullTextSearchColumnList');

        if (!$fulltextFieldList) {
            $this->assertNull($fulltextFieldList);
            return;
        }

        $query = "SHOW INDEX FROM `". Util::toCamelCase($entityName) ."` WHERE Index_type = 'FULLTEXT'";
        $sth = $pdo->prepare($query);
        $sth->execute();

        $rowList = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertNotEmpty($rowList);

        $result = [];
        foreach ($rowList as $row) {
            $result[] = Util::toCamelCase($row['Column_name']);
        }

        asort($fulltextFieldList);
        asort($result);

        $this->assertEquals($fulltextFieldList, $result);
    }
}
