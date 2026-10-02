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

use Espo\Entities\Email;
use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;

/**
 * @implements Loader<Email>
 */
class UserColumnsLoader implements Loader
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        $emailUser = $this->entityManager
            ->getRDBRepository(Email::RELATIONSHIP_EMAIL_USER)
            ->select([
                Email::USERS_COLUMN_IS_READ,
                Email::USERS_COLUMN_IS_IMPORTANT,
                Email::USERS_COLUMN_IN_TRASH,
                Email::USERS_COLUMN_IN_ARCHIVE,
            ])
            ->where([
                Attribute::DELETED => false,
                'userId' => $this->user->getId(),
                'emailId' => $entity->getId(),
            ])
            ->findOne();

        if (!$emailUser) {
            $entity->set(Email::USERS_COLUMN_IS_READ, null);
            $entity->clear(Email::USERS_COLUMN_IS_IMPORTANT);
            $entity->clear(Email::USERS_COLUMN_IN_TRASH);
            $entity->clear(Email::USERS_COLUMN_IN_ARCHIVE);

            return;
        }

        $values = [
            Email::USERS_COLUMN_IS_READ => $emailUser->get(Email::USERS_COLUMN_IS_READ),
            Email::USERS_COLUMN_IS_IMPORTANT => $emailUser->get(Email::USERS_COLUMN_IS_IMPORTANT),
            Email::USERS_COLUMN_IN_TRASH => $emailUser->get(Email::USERS_COLUMN_IN_TRASH),
            Email::USERS_COLUMN_IN_ARCHIVE => $emailUser->get(Email::USERS_COLUMN_IN_ARCHIVE),
            'isUsersSent' => $entity->getSentBy()?->getId() === $this->user->getId(),
        ];

        $entity->setMultiple($values);

        foreach ($values as $key => $value) {
            $entity->setFetched($key, $value);
        }
    }
}
