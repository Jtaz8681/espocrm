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

namespace Espo\Core\Upgrades;

use Espo\Core\Container;
use Espo\Core\Exceptions\Error;

abstract class Base
{
    protected ActionManager $actionManager;

    protected ?string $name = null;
    /** @var array<string, mixed> */
    protected array $params = [];

    const UPLOAD = 'upload';
    const INSTALL = 'install';
    const UNINSTALL = 'uninstall';
    const DELETE = 'delete';

    public function __construct(Container $container)
    {
        $this->actionManager = new ActionManager($this->name ?? '', $container, $this->params);
    }

    /**
     * @return array<string, mixed>
     * @throws Error
     */
    public function getManifest(): array
    {
        return $this->actionManager->getManifest();
    }

    /**
     * @return array<string, mixed>
     * @throws Error
     */
    public function getManifestById(string $processId): array
    {
        $actionClass = $this->actionManager->getActionClass(self::INSTALL);
        $actionClass->setProcessId($processId);

        return $actionClass->getManifest();
    }

    /**
     * @throws Error
     */
    public function upload(string $data): string
    {
        $this->actionManager->setAction(self::UPLOAD);

        return $this->actionManager->run($data);
    }

    /**
     * @param array<string, mixed> $data
     * @throws Error
     */
    public function install(array $data): mixed
    {
        $this->actionManager->setAction(self::INSTALL);

        return $this->actionManager->run($data);
    }

    /**
     * @param array<string, mixed> $data
     * @throws Error
     */
    public function uninstall(array $data): mixed
    {
        $this->actionManager->setAction(self::UNINSTALL);

        return $this->actionManager->run($data);
    }

    /**
     * @param array<string, mixed> $data
     * @throws Error
     */
    public function delete(array $data): mixed
    {
        $this->actionManager->setAction(self::DELETE);

        return $this->actionManager->run($data);
    }

    /**
     * @param array<string, mixed> $params
     * @throws Error
     */
    public function runInstallStep(string $stepName, array $params = []): void
    {
        $this->runActionStep(self::INSTALL, $stepName, $params);
    }

    /**
     * @param array<string, mixed> $params
     * @throws Error
     * @noinspection PhpSameParameterValueInspection
     */
    private function runActionStep(string $actionName, string $stepName, array $params = []): void
    {
        $actionClass = $this->actionManager->getActionClass($actionName);
        $methodName = 'step' . ucfirst($stepName);

        if (!method_exists($actionClass, $methodName)) {
            if (!empty($params['id'])) {
                $actionClass->setProcessId($params['id']);
                $actionClass->throwErrorAndRemovePackage("Step \"$stepName\" is not found.");
            }

            throw new Error('Step "'. $stepName .'" is not found.');
        }

        $actionClass->$methodName($params); // throw an Exception on error
    }
}
