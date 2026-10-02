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

namespace Espo\Core\Container;

use Espo\Core\Application\ApplicationParams;
use Espo\Core\Container;
use Espo\Core\Container\Container as ContainerInterface;

use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingLoader;
use Espo\Core\Binding\EspoBindingLoader;

use Espo\Core\Loaders\ApplicationState as ApplicationStateLoader;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Config\ConfigFileManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\Module;

use Espo\Core\Loaders\Log as LogLoader;
use Espo\Core\Loaders\DataManager as DataManagerLoader;
use Espo\Core\Loaders\Metadata as MetadataLoader;

/**
 * Builds a service container.
 */
class ContainerBuilder
{
    /** @var class-string<ContainerInterface & Container> */
    private string $containerClassName = Container::class;
    /** @var class-string<Configuration> */
    private string $containerConfigurationClassName = ContainerConfiguration::class;
    /** @var class-string */
    private string $configClassName = Config::class;
    /** @var class-string */
    private string $fileManagerClassName = FileManager::class;
    /** @var class-string */
    private string $dataCacheClassName = DataCache::class;
    /** @var class-string<Module> */
    private string $moduleClassName = Module::class;
    private ?BindingLoader $bindingLoader = null;
    /** @var array<string, object> */
    private $services = [];
    /** @var array<string, class-string<Loader>> */
    protected $loaderClassNames = [
        'log' => LogLoader::class,
        'dataManager' => DataManagerLoader::class,
        'metadata' => MetadataLoader::class,
        'applicationState' => ApplicationStateLoader::class,
    ];
    private ?ApplicationParams $params = null;

    public function withParams(?ApplicationParams $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function withBindingLoader(BindingLoader $bindingLoader): self
    {
        $this->bindingLoader = $bindingLoader;

        return $this;
    }

    /**
     * @param array<string, object> $services
     */
    public function withServices(array $services): self
    {
        foreach ($services as $key => $value) {
            $this->services[$key] = $value;
        }

        return $this;
    }

    /**
     * @param array<string, class-string<Loader>> $classNames
     * @noinspection PhpUnused
     */
    public function withLoaderClassNames(array $classNames): self
    {
        foreach ($classNames as $key => $value) {
            $this->loaderClassNames[$key] = $value;
        }

        return $this;
    }

    /**
     * @param class-string<ContainerInterface & Container> $containerClassName
     */
    public function withContainerClassName(string $containerClassName): self
    {
        $this->containerClassName = $containerClassName;

        return $this;
    }

    /**
     * @param class-string<Configuration> $containerConfigurationClassName
     */
    public function withContainerConfigurationClassName(string $containerConfigurationClassName): self
    {
        $this->containerConfigurationClassName = $containerConfigurationClassName;

        return $this;
    }

    /**
     * @param class-string $configClassName
     */
    public function withConfigClassName(string $configClassName): self
    {
        $this->configClassName = $configClassName;

        return $this;
    }

    /**
     * @param class-string $fileManagerClassName
     * @noinspection PhpUnused
     */
    public function withFileManagerClassName(string $fileManagerClassName): self
    {
        $this->fileManagerClassName = $fileManagerClassName;

        return $this;
    }

    /**
     * @param class-string $dataCacheClassName
     * @noinspection PhpUnused
     */
    public function withDataCacheClassName(string $dataCacheClassName): self
    {
        $this->dataCacheClassName = $dataCacheClassName;

        return $this;
    }

    public function build(): ContainerInterface
    {
       $this->services['applicationParams'] = $this->params ?? new ApplicationParams();

        /** @var Config $config */
        $config = $this->services['config'] ?? (
            new $this->configClassName(
                new ConfigFileManager()
            )
        );

        /** @var FileManager $fileManager */
        $fileManager = $this->services['fileManager'] ?? (
            new $this->fileManagerClassName(
                $config->get('defaultPermissions')
            )
        );

        /** @var DataCache $dataCache */
        $dataCache = $this->services['dataCache'] ?? (
            new $this->dataCacheClassName($fileManager)
        );

        $useCache = $config->get('useCache') ?? false;

        /** @var Module $module */
        $module = $this->services['module'] ?? (
            new $this->moduleClassName($fileManager, $dataCache, $useCache)
        );

        $systemConfig = new Config\SystemConfig($config);

        $this->services['config'] = $config;
        $this->services['fileManager'] = $fileManager;
        $this->services['dataCache'] = $dataCache;
        $this->services['module'] = $module;
        $this->services['systemConfig'] = $systemConfig;

        if ($this->params?->services) {
            $this->services = array_merge($this->services, $this->params->services);
        }

        $bindingLoader = $this->bindingLoader ?? (
            new EspoBindingLoader(
                module: $module,
                binding: $this->params?->binding,
            )
        );

        $bindingContainer = new BindingContainer($bindingLoader->load());

        return new $this->containerClassName(
            $this->containerConfigurationClassName,
            $bindingContainer,
            $this->loaderClassNames,
            $this->services
        );
    }
}
