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

namespace Espo\Core\Acl\Cache;

use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Entities\Portal;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @todo Clear cache in AclManager.
 */
class Clearer
{
    public function __construct(private FileManager $fileManager, private EntityManager $entityManager)
    {}

    public function clearForAllInternalUsers(): void
    {
        $this->fileManager->removeInDir('data/cache/application/acl');
        $this->fileManager->removeInDir('data/cache/application/aclMap');
    }

    public function clearForAllPortalUsers(): void
    {
        $this->fileManager->removeInDir('data/cache/application/aclPortal');
        $this->fileManager->removeInDir('data/cache/application/aclPortalMap');
    }

    public function clearForUser(User $user): void
    {
        if ($user->isPortal()) {
            $this->clearForPortalUser($user);

            return;
        }

        $part = basename($user->getId() . '.php');

        $this->fileManager->remove('data/cache/application/acl/' . $part);
        $this->fileManager->remove('data/cache/application/aclMap/' . $part);
    }

    private function clearForPortalUser(User $user): void
    {
        $portals = $this->entityManager
            ->getRDBRepositoryByClass(Portal::class)
            ->select(Attribute::ID)
            ->find();

        foreach ($portals as $portal) {
            $part = basename($portal->getId()) . '/' . basename($user->getId() . '.php');

            $this->fileManager->remove('data/cache/application/aclPortal/' . $part);
            $this->fileManager->remove('data/cache/application/aclPortalMap/' . $part);
        }
    }
}
