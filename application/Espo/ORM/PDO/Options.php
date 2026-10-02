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

class Options
{
    /**
     * @return array<int, mixed>
     */
    public static function getOptionsFromDatabaseParams(DatabaseParams $databaseParams): array
    {
        $options = [];

        if ($databaseParams->getSslCa()) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $databaseParams->getSslCa();
        }

        if ($databaseParams->getSslCert()) {
            $options[PDO::MYSQL_ATTR_SSL_CERT] = $databaseParams->getSslCert();
        }

        if ($databaseParams->getSslKey()) {
            $options[PDO::MYSQL_ATTR_SSL_KEY] = $databaseParams->getSslKey();
        }

        if ($databaseParams->getSslCaPath()) {
            $options[PDO::MYSQL_ATTR_SSL_CAPATH] = $databaseParams->getSslCaPath();
        }

        if ($databaseParams->getSslCipher()) {
            $options[PDO::MYSQL_ATTR_SSL_CIPHER] = $databaseParams->getSslCipher();
        }

        if ($databaseParams->isSslVerifyDisabled()) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        return $options;
    }
}
