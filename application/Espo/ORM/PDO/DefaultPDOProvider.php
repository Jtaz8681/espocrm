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

class DefaultPDOProvider implements PDOProvider
{
    private ?PDO $pdo = null;

    public function __construct(
        private DatabaseParams $databaseParams,
        private PDOFactory $pdoFactory
    ) {}

    public function get(): PDO
    {
        if (!$this->pdo) {
            $this->intPDO();
        }

        assert($this->pdo !== null);

        return $this->pdo;
    }

    private function intPDO(): void
    {
        $this->pdo = $this->pdoFactory->create($this->databaseParams);
    }
}
