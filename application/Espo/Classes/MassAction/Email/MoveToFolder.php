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

namespace Espo\Classes\MassAction\Email;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\QueryBuilder;
use Espo\Core\MassAction\Result;
use Espo\Entities\Email;
use Espo\Entities\EmailFolder;
use Espo\Entities\GroupEmailFolder;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Tools\Email\Folder;
use Espo\Tools\Email\InboxService as EmailService;
use Exception;
use RuntimeException;

class MoveToFolder implements MassAction
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private EntityManager $entityManager,
        private EmailService $service,
        private User $user
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function process(Params $params, Data $data): Result
    {
        $folderId = $data->get('folderId');

        if (!is_string($folderId)) {
            throw new BadRequest("No folder ID.");
        }

        if (
            $folderId !== Folder::INBOX &&
            $folderId !== Folder::ARCHIVE &&
            !str_starts_with($folderId, 'group:')
        ) {
            $folder = $this->entityManager
                ->getRDBRepositoryByClass(EmailFolder::class)
                ->where([
                    'assignedUserId' => $this->user->getId(),
                    'id' => $folderId,
                ])
                ->findOne();

            if (!$folder) {
                throw new Forbidden("Folder not found.");
            }
        }

        if ($folderId && str_starts_with($folderId, 'group:')) {
            $folder = $this->entityManager
                ->getRDBRepositoryByClass(GroupEmailFolder::class)
                ->where([Attribute::ID => substr($folderId, 6)])
                ->findOne();

            if (!$folder) {
                throw new Forbidden("Group folder not found.");
            }
        }

        try {
            $query = $this->queryBuilder->build($params);
        } catch (BadRequest|Forbidden $e) {
            throw new RuntimeException($e->getMessage());
        }

        $collection = $this->entityManager
            ->getRDBRepositoryByClass(Email::class)
            ->clone($query)
            ->sth()
            ->select([Attribute::ID])
            ->find();

        $count = 0;

        foreach ($collection as $email) {
            try {
                $this->service->moveToFolder($email->getId(), $folderId, $this->user->getId());
            } catch (Exception) {
                continue;
            }

            $count++;
        }

        return new Result($count);
    }
}
