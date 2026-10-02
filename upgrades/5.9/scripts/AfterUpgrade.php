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

class AfterUpgrade
{
    public function run($container)
    {
        $this->container = $container;

        $config = $container->get('config');
        $config->set('busyRangesEntityList', ['Meeting', 'Call']);
        $config->set('emailAddressLookupEntityTypeList', ['User', 'Contact', 'Lead', 'Account']);
        $config->save();

        $this->fixCollation($container);
    }

    protected function fixCollation($container)
    {
        $ignotedEntityList = ['Job', 'LayoutRecord'];

        $pdo = $container->get('entityManager')->getPDO();
        $ormMeta = $container->get('ormMetadata')->getData(true);

        $fieldListExceededIndexMaxLength = \Espo\Core\Utils\Database\Schema\Utils::getFieldListExceededIndexMaxLength($ormMeta, 767);

        foreach ($ormMeta as $entityName => $entityParams) {

            if (in_array($entityName, $ignotedEntityList)) continue;
            if (!isset($fieldListExceededIndexMaxLength[$entityName])) continue;

            $tableName = \Espo\Core\Utils\Util::toUnderScore($entityName);

            //Get table columns params
            $query = "SHOW FULL COLUMNS FROM `". $tableName ."` WHERE `Collation` <> 'utf8mb4_unicode_ci'";

            try {
                $sth = $pdo->prepare($query);
                $sth->execute();
            } catch (\Exception $e) {
                $GLOBALS['log']->debug('Utf8mb4: Table does not exist - ' . $e->getMessage());
                continue;
            }

            $columnParams = array();
            $rowList = $sth->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rowList as $row) {
                $columnParams[ $row['Field'] ] = $row;
            }
            //END: get table columns params

            foreach ($entityParams['fields'] as $fieldName => $fieldParams) {

                $columnName = \Espo\Core\Utils\Util::toUnderScore($fieldName);

                if (!in_array($fieldName, $fieldListExceededIndexMaxLength[$entityName])) continue;
                if (!isset($columnParams[$columnName])) continue;

                if (isset($fieldParams['notStorable']) && $fieldParams['notStorable']) {
                    continue;
                }

                if (!isset($columnParams[$columnName]) || empty($columnParams[$columnName]['Type'])) {
                    continue;
                }

                $query = null;

                switch ($fieldParams['type']) {
                    case 'varchar':
                    case 'text':
                    case 'jsonObject':
                    case 'jsonArray':
                        $query = "ALTER TABLE `".$tableName."` CHANGE COLUMN `". $columnName ."` `". $columnName ."` ". $columnParams[$columnName]['Type'] ." CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
                        break;
                }

                if (!empty($query)) {
                    $GLOBALS['log']->debug('Utf8mb4: execute the query - [' . $query . '].');

                    try {
                        $sth = $pdo->prepare($query);
                        $sth->execute();
                    } catch (\Exception $e) {
                        $GLOBALS['log']->warning('Utf8mb4: FAILED executing the query - [' . $query . '], details: '. $e->getMessage() .'.');
                    }
                }
            }
        }
    }
}
