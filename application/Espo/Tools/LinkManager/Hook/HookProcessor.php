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

namespace Espo\Tools\LinkManager\Hook;

use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;
use Espo\Tools\LinkManager\Params;

class HookProcessor
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    public function processCreate(Params $params): void
    {
        foreach ($this->getCreateHookList() as $hook) {
            $hook->process($params);
        }
    }

    public function processDelete(Params $params): void
    {
        foreach ($this->getDeleteHookList() as $hook) {
            $hook->process($params);
        }
    }

    /**
     * @return CreateHook[]
     */
    private function getCreateHookList(): array
    {
        /** @var class-string<CreateHook>[] $classNameList */
        $classNameList = $this->metadata->get(['app', 'linkManager', 'createHookClassNameList']) ?? [];

        $list = [];

        foreach ($classNameList as $className) {
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return DeleteHook[]
     */
    private function getDeleteHookList(): array
    {
        /** @var class-string<DeleteHook>[] $classNameList */
        $classNameList = $this->metadata->get(['app', 'linkManager', 'deleteHookClassNameList']) ?? [];

        $list = [];

        foreach ($classNameList as $className) {
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }
}
