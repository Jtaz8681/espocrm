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

namespace Espo\ORM\PDO;

use Espo\ORM\DatabaseParams;
use PDO;
use RuntimeException;

class PostgresqlPDOFactory implements PDOFactory
{
    private const DEFAULT_CHARSET = 'utf8';

    public function create(DatabaseParams $databaseParams): PDO
    {
        $platform = strtolower($databaseParams->getPlatform() ?? '');

        $host = $databaseParams->getHost();
        $port = $databaseParams->getPort();
        $dbname = $databaseParams->getName();
        $charset = $databaseParams->getCharset() ?? self::DEFAULT_CHARSET;
        $username = $databaseParams->getUsername();
        $password = $databaseParams->getPassword();

        if (!$platform) {
            throw new RuntimeException("No 'platform' parameter.");
        }

        if (!$host) {
            throw new RuntimeException("No 'host' parameter.");
        }

        $dsn = 'pgsql:' . 'host=' . $host;

        if ($port) {
            $dsn .= ';' . 'port=' . (string) $port;
        }

        if ($dbname) {
            $dsn .= ';' . 'dbname=' . $dbname;
        }

        $dsn .= ';' . 'options=' . "'--client_encoding={$charset}'";

        $options = Options::getOptionsFromDatabaseParams($databaseParams);

        $pdo = new PDO($dsn, $username, $password, $options);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->query("SET time zone 'UTC'");

        return $pdo;
    }
}
