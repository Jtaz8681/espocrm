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

namespace Espo\Core\Action;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

class ActionFactory
{
    public function __construct(private Metadata $metadata, private InjectableFactory $injectableFactory)
    {}

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function create(string $action, ?string $entityType = null): Action
    {
        $className = $this->getClassName($action, $entityType);

        if (!$className) {
            throw new NotFound("Action '{$action}' not found.");
        }

        if ($entityType && $this->isDisabled($action, $entityType)) {
            throw new Forbidden("Action '{$action}' is disabled for '{$entityType}'.");
        }

        return $this->injectableFactory->create($className);
    }

    /**
     * @param array<string,object> $with
     * @throws NotFound
     * @throws Forbidden
     * @todo Remove.
     * @deprecated
     */
    public function createWith(string $action, ?string $entityType, array $with): Action
    {
        $className = $this->getClassName($action, $entityType);

        if (!$className) {
            throw new NotFound("Action '{$action}' not found.");
        }

        if ($entityType && $this->isDisabled($action, $entityType)) {
            throw new Forbidden("Action '{$action}' is disabled for '{$entityType}'.");
        }

        return $this->injectableFactory->createWith($className, $with);
    }

    /**
     * @return ?class-string<Action>
     */
    private function getClassName(string $action, ?string $entityType): ?string
    {
        if ($entityType) {
            $className = $this->getEntityTypeClassName($action, $entityType);

            if ($className) {
                return $className;
            }
        }

        /** @var ?class-string<Action> */
        return $this->metadata->get(
            ['app', 'actions', $action, 'implementationClassName']
        );
    }

    /**
     * @return ?class-string<Action>
     */
    private function getEntityTypeClassName(string $action, string $entityType): ?string
    {
        /** @var ?class-string<Action> */
        return  $this->metadata->get(
            ['recordDefs', $entityType, 'actions', $action, 'implementationClassName']
        );
    }

    private function isDisabled(string $action, string $entityType): bool
    {
        $actionsDisabled = $this->metadata
            ->get(['recordDefs', $entityType, 'actionsDisabled']) ?? false;

        if ($actionsDisabled) {
            return true;
        }

        if ($this->needsToBeAllowed($entityType)) {
            if (!$this->isAllowed($action, $entityType)) {
                return true;
            }
        }

        return $this->metadata
            ->get(['recordDefs', $entityType, 'actions', $action, 'disabled']) ?? false;
    }

    private function needsToBeAllowed(string $entityType): bool
    {
        $isObject = $this->metadata->get(['scopes', $entityType, 'object']) ?? false;

        if (!$isObject) {
            return true;
        }

        return $this->metadata
            ->get(['recordDefs', $entityType, 'notAllowedActionsDisabled']) ?? false;
    }

    private function isAllowed(string $action, string $entityType): bool
    {
        return $this->metadata
            ->get(['recordDefs', $entityType, 'actions', $action, 'allowed']) ?? false;
    }
}
