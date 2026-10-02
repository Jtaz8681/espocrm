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

namespace tests\integration\Espo\Core\Utils\Database;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Database\Helper;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use tests\integration\Core\BaseTestCase;

abstract class Base extends BaseTestCase
{
    protected ?string $dataFile = 'InitData.php';
    protected ?string $pathToFiles = 'Core/Database/customFiles';

    protected function beforeSetUp(): void
    {}

    protected function getColumnInfo($entityName, $fieldName)
    {
        $pdo = $this->getInjectableFactory()
            ->create(Helper::class)
            ->getPDO();

        $dbName = $this->getContainer()
            ->getByClass(Config::class)
            ->get('database.dbname');

        $query = "
            SELECT * FROM information_schema.columns
            WHERE table_name = '" . Util::toUnderScore($entityName) . "'
            AND column_name = '" . Util::toUnderScore($fieldName) . "'
            AND table_schema = '" . $dbName . "'
        ";

        $sth = $pdo->prepare($query);
        $sth->execute();

        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    protected function updateDefs($entityName, $fieldName, array $fieldDefs = [], ?array $linkDefs = null)
    {
        $metadata = $this->getContainer()->getByClass(Metadata::class);

        $entityDefs = $metadata->get(['entityDefs', $entityName]);

        if (empty($entityDefs)) {
            return;
        }

        $save = false;

        if (!empty($fieldDefs)) {
            $currentFieldDefs = $entityDefs['fields'][$fieldName] ?? [];
            $entityDefs['fields'][$fieldName] = array_merge($currentFieldDefs, $fieldDefs);
            $save = true;
        }

        if (!empty($linkDefs)) {
            $currentLinkDefs = $entityDefs['links'][$fieldName] ?? [];
            $entityDefs['links'][$fieldName] = array_merge($currentLinkDefs, $linkDefs);
            $save = true;
        }

        if ($save) {
            $metadata->set('entityDefs', 'Test', $entityDefs);
            $metadata->save();

            $this->getDataManager()->rebuild([$entityName]);
        }
    }

    protected function executeQuery($query)
    {
        $pdo = $this->getInjectableFactory()
            ->create(Helper::class)
            ->getPDO();

        $sth = $pdo->prepare($query);
        $sth->execute();
    }
}
