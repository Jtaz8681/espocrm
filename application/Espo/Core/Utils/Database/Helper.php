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

namespace Espo\Core\Utils\Database;

use Doctrine\DBAL\Connection as DbalConnection;

use Espo\Core\ORM\DatabaseParamsFactory;
use Espo\Core\ORM\PDO\PDOFactoryFactory;
use Espo\Core\Utils\Database\Dbal\ConnectionFactoryFactory as DBALConnectionFactoryFactory;
use Espo\ORM\DatabaseParams;

use PDO;
use RuntimeException;

class Helper
{
    private ?DbalConnection $dbalConnection = null;
    private ?PDO $pdo = null;

    public function __construct(
        private PDOFactoryFactory $pdoFactoryFactory,
        private DBALConnectionFactoryFactory $dbalConnectionFactoryFactory,
        private ConfigDataProvider $configDataProvider,
        private DetailsProviderFactory $detailsProviderFactory,
        private DatabaseParamsFactory $databaseParamsFactory
    ) {}

    public function getDbalConnection(): DbalConnection
    {
        if (!isset($this->dbalConnection)) {
            $this->dbalConnection = $this->createDbalConnection();
        }

        return $this->dbalConnection;
    }

    public function getPDO(): PDO
    {
        if (!isset($this->pdo)) {
            $this->pdo = $this->createPDO();
        }

        return $this->pdo;
    }

    /**
     * Clone with another PDO connection.
     */
    public function withPDO(PDO $pdo): self
    {
        $obj = clone $this;
        $obj->pdo = $pdo;
        $obj->dbalConnection = null;

        return $obj;
    }

    /**
     * Create a PDO connection.
     */
    public function createPDO(?DatabaseParams $params = null): PDO
    {
        $params = $params ?? $this->databaseParamsFactory->create();

        return $this->pdoFactoryFactory
            ->create($params->getPlatform() ?? '')
            ->create($params);
    }

    private function createDbalConnection(): DbalConnection
    {
        $params = $this->databaseParamsFactory->create();

        $platform = $params->getPlatform();

        if (!$platform) {
            throw new RuntimeException("No database platform.");
        }

        return $this->dbalConnectionFactoryFactory
            ->create($platform, $this->getPDO())
            ->create($params);
    }

    /**
     * Get a database type (MySQL, MariaDB, PostgreSQL).
     */
    public function getType(): string
    {
        return $this->createDetailsProvider()->getType();
    }

    /**
     * Get a database version.
     */
    public function getVersion(): string
    {
        return $this->createDetailsProvider()->getVersion();
    }

    /**
     * Get a database parameter.
     */
    public function getParam(string $name): ?string
    {
        return $this->createDetailsProvider()->getParam($name);
    }

    /**
     * Get a database server version string.
     */
    public function getServerVersion(): string
    {
        return $this->createDetailsProvider()->getServerVersion();
    }

    private function createDetailsProvider(): DetailsProvider
    {
        $platform = $this->configDataProvider->getPlatform();

        return $this->detailsProviderFactory->create($platform, $this->getPDO());
    }
}
