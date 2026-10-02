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

namespace Espo\Core\Upgrades\Actions;

use Espo\Core\Exceptions\Error;
use Espo\Entities\Extension;
use Espo\ORM\EntityManager;
use RuntimeException;

class Helper
{
    private ?Base $actionObject = null;

    public function __construct(private EntityManager $entityManager)
    {}

    public function setActionObject(Base $actionObject): void
    {
        $this->actionObject = $actionObject;
    }

    /**
     * Check dependencies.
     *
     * @param array<string, string[]|string> $dependencyList
     * @throws Error
     */
    public function checkDependencies(mixed $dependencyList): bool
    {
        if (!$this->actionObject) {
            throw new RuntimeException("No action passed.");
        }

        if (!is_array($dependencyList)) {
            $dependencyList = (array) $dependencyList;
        }

        /** @var array<string, string[]|string> $dependencyList */

        foreach ($dependencyList as $extensionName => $extensionVersion) {
            $entity = $this->entityManager
                ->getRDBRepositoryByClass(Extension::class)
                ->where([
                    'name' => trim($extensionName),
                    'isInstalled' => true,
                ])
                ->findOne();

            $versionString = is_array($extensionVersion) ?
                implode(', ', $extensionVersion) :
                $extensionVersion;

            $errorMessage = "Dependency error: Extension '$extensionName' with version '$versionString' is missing.";

            if (
                !$entity ||
                !$this->actionObject->checkVersions(
                    $extensionVersion,
                    $entity->getVersion(),
                    $errorMessage
                )
            ) {
                throw new Error($errorMessage);
            }
        }

        return true;
    }
}
