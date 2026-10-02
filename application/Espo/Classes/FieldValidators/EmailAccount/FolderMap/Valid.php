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

namespace Espo\Classes\FieldValidators\EmailAccount\FolderMap;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Entities\EmailAccount;
use Espo\Entities\EmailFolder;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use stdClass;

/**
 * @implements Validator<EmailAccount>
 */
class Valid implements Validator
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $map = $entity->get('folderMap');

        if ($map === null) {
            return null;
        }

        if (!$map instanceof stdClass) {
            return Failure::create();
        }

        foreach (get_object_vars($map) as $folderId) {
            if ($folderId === null) {
                continue;
            }

            if (!is_string($folderId)) {
                return Failure::create();
            }

            $folderEntry = $this->entityManager->getRepositoryByClass(EmailFolder::class)->getById($folderId);

            if (!$folderEntry) {
                return Failure::create();
            }
        }

        return null;
    }
}
