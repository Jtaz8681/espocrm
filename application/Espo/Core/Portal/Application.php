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

namespace Espo\Core\Portal;

use Espo\Core\Application\ApplicationParams;
use Espo\Entities\Portal;
use Espo\ORM\EntityManager;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Application as BaseApplication;
use Espo\Core\Container\ContainerBuilder;
use Espo\Core\Portal\Container as PortalContainer;
use Espo\Core\Portal\Container\ContainerConfiguration as PortalContainerConfiguration;
use Espo\Core\Portal\Utils\Config;
use LogicException;

class Application extends BaseApplication
{
    /**
     * @throws Forbidden
     * @throws NotFound
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(
        ?string $portalId,
        ?ApplicationParams $params = null,
    ) {
        date_default_timezone_set('UTC');

        $this->initContainer($params);
        $this->initPortal($portalId);
        $this->initAutoloads();
        $this->initPreloads();
    }

    protected function initContainer(?ApplicationParams $params): void
    {
        $container = (new ContainerBuilder())
            ->withConfigClassName(Config::class)
            ->withContainerClassName(PortalContainer::class)
            ->withContainerConfigurationClassName(PortalContainerConfiguration::class)
            ->withParams($params)
            ->build();

        if (!$container instanceof PortalContainer) {
            throw new LogicException("Wrong container created.");
        }

        $this->container = $container;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    protected function initPortal(?string $portalId): void
    {
        if (!$portalId) {
            throw new LogicException("Portal ID was not passed to Portal\Application.");
        }

        $entityManager = $this->container->getByClass(EntityManager::class);

        $portal = $entityManager->getEntityById(Portal::ENTITY_TYPE, $portalId);

        if (!$portal) {
            $portal = $entityManager
                ->getRDBRepositoryByClass(Portal::class)
                ->where(['customId' => $portalId])
                ->findOne();
        }

        if (!$portal) {
            throw new NotFound("Portal $portalId not found.");
        }

        if (!$portal->isActive()) {
            throw new Forbidden("Portal $portalId is not active.");
        }

        $container = $this->container;

        if (!$container instanceof PortalContainer) {
            throw new LogicException();
        }

        $container->setPortal($portal);
    }

    protected function initPreloads(): void
    {
        parent::initPreloads();

        foreach ($this->getMetadata()->get(['app', 'portalContainerServices']) ?? [] as $name => $defs) {
            if ($defs['preload'] ?? false) {
                $this->container->get($name);
            }
        }
    }
}
