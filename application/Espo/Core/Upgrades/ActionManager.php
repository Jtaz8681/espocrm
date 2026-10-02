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
use Espo\Core\Upgrades\Actions\Base as ActionBase;

class ActionManager
{
    private string $managerName;
    private Container $container;
    /** @var array<string, array<string, ActionBase>> */
    private $objects;
    protected ?string $currentAction;
    /** @var array<string, mixed> */
    protected array $params;

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(string $managerName, Container $container, array $params)
    {
        $this->managerName = $managerName;
        $this->container = $container;

        $params['name'] = $managerName;
        $this->params = $params;
    }

    protected function getManagerName(): string
    {
        return $this->managerName;
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    public function setAction(string $action): void
    {
        $this->currentAction = $action;
    }

    public function getAction(): string
    {
        assert($this->currentAction !== null);

        return $this->currentAction;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @throws Error
     */
    public function run(mixed $data): mixed
    {
        $object = $this->getObject();

        return $object->run($data);
    }

    /**
     * @throws Error
     */
    public function getActionClass(string $actionName): ActionBase
    {
        return $this->getObject($actionName);
    }

    /**
     * @return array<string, mixed>
     * @throws Error
     */
    public function getManifest(): array
    {
        return $this->getObject()->getManifest();
    }

    /**
     * @param ?string $actionName
     * @throws Error
     */
    protected function getObject(?string $actionName = null): ActionBase
    {
        $managerName = $this->getManagerName();

        if (!$actionName) {
            $actionName = $this->getAction();
        }

        if (!isset($this->objects[$managerName][$actionName])) {
            $class = "Espo\\Core\\Upgrades\\Actions\\" . ucfirst($managerName) . '\\' . ucfirst($actionName);

            if (!class_exists($class)) {
                throw new Error('Could not find an action ['.ucfirst($actionName).'], class ['.$class.'].');
            }

            /** @var class-string<ActionBase> $class */

            $this->objects[$managerName][$actionName] = new $class($this->container, $this);
        }

        return $this->objects[$managerName][$actionName];
    }
}
