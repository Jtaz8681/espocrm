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

namespace Espo\Core;

use Espo\Core\Application\ApplicationParams;
use Espo\Core\Application\Runner;
use Espo\Core\Application\RunnerParameterized;
use Espo\Core\Container\ContainerBuilder;
use Espo\Core\Application\RunnerRunner;
use Espo\Core\Application\Runner\Params as RunnerParams;
use Espo\Core\Application\Exceptions\RunnerException;
use Espo\Core\Utils\Autoload;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\ClientManager;
use RuntimeException;

/**
 * A central access point of the application.
 */
class Application
{
    protected Container $container;

    public function __construct(
        ?ApplicationParams $params = null,
    ) {
        date_default_timezone_set('UTC');

        $this->initContainer($params);
        $this->initAutoloads();
        $this->initPreloads();
    }

    protected function initContainer(?ApplicationParams $params): void
    {
        $container = (new ContainerBuilder())
            ->withParams($params)
            ->build();

        if (!$container instanceof Container) {
            throw new RuntimeException();
        }

        $this->container = $container;
    }

    /**
     * Run an application runner.
     *
     * @param class-string<Runner|RunnerParameterized> $className A runner class name.
     * @param ?RunnerParams $params Runner parameters.
     */
    public function run(string $className, ?RunnerParams $params = null): void
    {
        $runnerRunner = $this->getInjectableFactory()->create(RunnerRunner::class);

        try {
            $runnerRunner->run($className, $params);
        } catch (RunnerException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Whether the application is installed.
     */
    public function isInstalled(): bool
    {
        return $this->getConfig()->get('isInstalled');
    }

    /**
     * Get the service container.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @since 10.0.0
     */
    public function getInjectableFactory(): InjectableFactory
    {
        return $this->container->getByClass(InjectableFactory::class);
    }

    protected function getApplicationUser(): ApplicationUser
    {
        return $this->container->getByClass(ApplicationUser::class);
    }

    protected function getClientManager(): ClientManager
    {
        return $this->container->getByClass(ClientManager::class);
    }

    protected function getMetadata(): Metadata
    {
        return $this->container->getByClass(Metadata::class);
    }

    protected function getConfig(): Config
    {
        return $this->container->getByClass(Config::class);
    }

    protected function initAutoloads(): void
    {
        $autoload = $this->getInjectableFactory()->create(Autoload::class);

        $autoload->register();
    }

    /**
     * Initialize services that has the 'preload' parameter.
     */
    protected function initPreloads(): void
    {
        foreach ($this->getMetadata()->get(['app', 'containerServices']) ?? [] as $name => $defs) {
            if ($defs['preload'] ?? false) {
                $this->container->get($name);
            }
        }
    }

    /**
     * Set a base path of an index file related to the application directory. Used for a portal.
     */
    public function setClientBasePath(string $basePath): void
    {
        $this->getClientManager()->setBasePath($basePath);
    }

    /**
     * Set up the system user. The system user is used when no user is logged in.
     */
    public function setupSystemUser(): void
    {
        $this->getApplicationUser()->setupSystemUser();
    }
}
