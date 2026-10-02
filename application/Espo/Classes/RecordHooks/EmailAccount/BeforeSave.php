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

namespace Espo\Classes\RecordHooks\EmailAccount;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\EmailAccount;
use Espo\Entities\EmailFolder;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use RuntimeException;
use stdClass;

/**
 * @implements SaveHook<EmailAccount>
 */
class BeforeSave implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
    ) {}

    public function process(Entity $entity): void
    {
        $this->checkFolderMap($entity);
    }

    /**
     * @throws Forbidden
     */
    private function checkFolderMap(EmailAccount $entity): void
    {
        if (!$entity->isAttributeChanged('folderMap')) {
            return;
        }

        $map = $entity->get('folderMap');

        if ($map === null) {
            return;
        }

        if (!$map instanceof stdClass) {
            throw new RuntimeException("Bad folderMap.");
        }

        foreach (get_object_vars($map) as $folderId) {
            if ($folderId === null) {
                continue;
            }

            if (!is_string($folderId)) {
                throw new RuntimeException("Bad folderMap item.");
            }

            $folderEntry = $this->entityManager->getRepositoryByClass(EmailFolder::class)->getById($folderId);

            if (!$folderEntry) {
                throw Forbidden::createWithBody('noFolder',
                    Body::create()->withMessageTranslation('noFolder', EmailAccount::ENTITY_TYPE)
                );
            }

            if (!$this->acl->checkEntityRead($folderEntry)) {
                throw new Forbidden("No access to mapped folder $folderId.");
            }
        }
    }
}
