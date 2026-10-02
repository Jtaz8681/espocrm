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

namespace Espo\Core\EntryPoint;

use Espo\Core\Exceptions\NotFound;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\ClassFinder;

/**
 * Runs entry points.
 */
class EntryPointManager
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private ClassFinder $classFinder
    ) {}

    /**
     * @throws NotFound
     */
    public function checkAuthRequired(string $name): bool
    {
        $className = $this->getClassName($name);

        if (!$className) {
            throw new NotFoundSilent("Entry point '$name' not found.");
        }

        $noAuth = false;

        if (isset($className::$noAuth)) {
            $noAuth = $className::$noAuth;
        }

        if ($noAuth) {
            return false;
        }

        // for backward compatibility
        return $className::$authRequired ?? true;
    }

    /**
     * @throws NotFound
     */
    public function run(string $name, Request $request, Response $response): void
    {
        $className = $this->getClassName($name);

        if (!$className) {
            throw new NotFoundSilent("Entry point '$name' not found.");
        }

        $entryPoint = $this->injectableFactory->create($className);

        $entryPoint->run($request, $response);
    }

    /**
     * @return ?class-string<EntryPoint>
     */
    private function getClassName(string $name): ?string
    {
        /** @var ?class-string<EntryPoint> */
        return $this->classFinder->find('EntryPoints', ucfirst($name));
    }
}
