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

namespace Espo\Core\Rebuild;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

class RebuildActionProcessor
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata
    ) {}

    public function process(): void
    {
        foreach ($this->getActionList() as $action) {
            $action->process();
        }
    }

    /**
     * @return RebuildAction[]
     */
    private function getActionList(): array
    {
        $classNameList = $this->getClassNameList();

        $list = [];

        foreach ($classNameList as $className) {
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return class-string<RebuildAction>[]
     */
    private function getClassNameList(): array
    {
        /** @var class-string<RebuildAction>[] */
        return $this->metadata->get(['app', 'rebuild', 'actionClassNameList']) ?? [];
    }
}
