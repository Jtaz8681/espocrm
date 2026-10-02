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

namespace Espo\Core\Console\Commands;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\Core\AclManager;
use Espo\Core\Console\Command;
use Espo\Core\Console\Command\Params;
use Espo\Core\Console\IO;
use Espo\Core\Container;
use Espo\Core\Portal\Application as PortalApplication;
use Espo\Core\Acl\Table;

/**
 * Checks access for websocket topic subscription. Prints `true` if access allowed.
 *
 * @noinspection PhpUnused
 */
class AclCheck implements Command
{
    public function __construct(private Container $container)
    {}

    public function run(Params $params, IO $io): void
    {
        $userId = $params->getOption('userId');
        $scope = $params->getOption('scope');
        $id = $params->getOption('id');
        /** @var Table::ACTION_*|null $action */
        $action = $params->getOption('action');

        $io->setExitStatus(1);

        if (!$userId || !$scope) {
            return;
        }

        if ($params->hasOption('id') && !$id) {
            return;
        }

        $entityManager = $this->container->getByClass(EntityManager::class);

        $user = $entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

        if (!$user) {
            return;
        }

        if ($user->isPortal()) {
            $this->processPortal(
                io: $io,
                userId: $userId,
                scope: $scope,
                action: $action,
                id: $id,
                user: $user,
            );
        }

        if (!$this->check($user, $scope, $id, $action, $this->container)) {
            return;
        }

        $io->setExitStatus(0);
        $io->write('true');
    }

    /**
     * @param Table::ACTION_*|null $action
     * @noinspection PhpDocSignatureInspection
     */
    private function check(
        User $user,
        string $scope,
        ?string $id,
        ?string $action,
        Container $container
    ): bool {

        if (!$id) {
            $aclManager = $container->getByClass(AclManager::class);

            return $aclManager->check($user, $scope, $action);
        }

        $entityManager = $container->getByClass(EntityManager::class);

        $entity = $entityManager->getEntityById($scope, $id);

        if (!$entity) {
            return false;
        }

        $aclManager = $container->getByClass(AclManager::class);

        return $aclManager->check($user, $entity, $action);
    }

    /**
     * @param Table::ACTION_*|null $action
     * @noinspection PhpDocSignatureInspection
     */
    private function processPortal(
        IO $io,
        string $userId,
        string $scope,
        ?string $action,
        ?string $id,
        User $user,
    ): void {

        $portalIds = $user->getLinkMultipleIdList('portals');

        foreach ($portalIds as $portalId) {
            try {
                $application = new PortalApplication($portalId);
            } catch (Forbidden|NotFound) {
                return;
            }

            $containerPortal = $application->getContainer();
            $entityManager = $containerPortal->getByClass(EntityManager::class);

            $user = $entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

            if (!$user) {
                return;
            }

            $result = $this->check($user, $scope, $id, $action, $containerPortal);

            if (!$result) {
                continue;
            }

            $io->setExitStatus(0);
            $io->write('true');

            return;
        }
    }
}
