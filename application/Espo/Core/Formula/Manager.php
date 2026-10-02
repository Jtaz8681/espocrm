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

namespace Espo\Core\Formula;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;

use stdClass;

/**
 * An access point for the formula functionality.
 */
class Manager
{
    private Evaluator $evaluator;

    public function __construct(InjectableFactory $injectableFactory, Metadata $metadata)
    {
        $functionClassNameMap = $metadata->get(['app', 'formula', 'functionClassNameMap'], []);

        $unsafeFunctionList = $this->getUnsafeFunctionList($metadata);

        $this->evaluator = new Evaluator($injectableFactory, $functionClassNameMap, $unsafeFunctionList);
    }

    /**
     * Executes a script and returns its result.
     *
     * @throws Exceptions\Error
     */
    public function run(string $script, ?Entity $entity = null, ?stdClass $variables = null) : mixed
    {
        return $this->evaluator->process($script, $entity, $variables);
    }

    /**
     * Executes a script in safe mode and returns its result.
     *
     * @throws Exceptions\Error
     * @since 8.3.0
     * @internal
     */
    public function runSafe(string $script, ?Entity $entity = null, ?stdClass $variables = null): mixed
    {
        return $this->evaluator->processSafe($script, $entity, $variables);
    }

    /**
     * @return string[]
     */
    private function getUnsafeFunctionList(Metadata $metadata): array
    {
        $unsafeFunctionList = [];

        foreach ($metadata->get("app.formula.functionList") ?? [] as $item) {
            if ($item['unsafe'] ?? false) {
                $unsafeFunctionList[] = $item['name'];
            }
        }
        return $unsafeFunctionList;
    }
}
