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

namespace Espo\Classes\FieldProcessing\Email;

use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Entities\Email;
use Espo\Entities\EmailFolder;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @implements Loader<Email>
 */
class FolderDataLoader implements Loader
{
    public function __construct(private EntityManager $entityManager) {}

    public function process(Entity $entity, Params $params): void
    {
        $folderId = $entity->get(Email::USERS_COLUMN_FOLDER_ID);

        if (!$folderId) {
            return;
        }

        $folder = $this->entityManager
            ->getRDBRepositoryByClass(EmailFolder::class)
            ->select([Attribute::ID, 'name'])
            ->where([Attribute::ID => $folderId])
            ->findOne();

        if (!$folder) {
            return;
        }

        $entity->set('folderName', $folder->getName());
    }
}
