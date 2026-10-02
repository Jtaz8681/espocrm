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

namespace Espo\Core\Binding;

use Espo\Core\Utils\Module;
use Espo\Binding;

class EspoBindingLoader implements BindingLoader
{
    /** @var string[] */
    private array $moduleNameList;

    public function __construct(
        Module $module,
        private ?BindingProcessor $binding = null,
    ) {
        $this->moduleNameList = $module->getOrderedList();
    }

    public function load(): BindingData
    {
        $data = new BindingData();
        $binder = new Binder($data);

        (new Binding())->process($binder);

        foreach ($this->moduleNameList as $moduleName) {
            $this->loadModule($binder, $moduleName);
        }

        $this->loadCustom($binder);

        $this->binding?->process($binder);

        return $data;
    }

    private function loadModule(Binder $binder, string $moduleName): void
    {
        $className = 'Espo\\Modules\\' . $moduleName . '\\Binding';

        if (!class_exists($className)) {
            return;
        }

        /** @var class-string<BindingProcessor> $className */

        (new $className())->process($binder);
    }

    private function loadCustom(Binder $binder): void
    {
        /** @var class-string<BindingProcessor>|string $className */
        $className = 'Espo\\Custom\\Binding';

        if (!class_exists($className)) {
            return;
        }

        /** @var class-string<BindingProcessor> $className */

        (new $className())->process($binder);
    }
}
