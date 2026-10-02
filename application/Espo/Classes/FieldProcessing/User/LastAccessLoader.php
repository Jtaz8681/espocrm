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

namespace Espo\Classes\FieldProcessing\User;

use Espo\Core\Name\Field;
use Espo\Entities\AuthLogRecord;
use Espo\Entities\AuthToken;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

use DateTime;
use Espo\ORM\Name\Attribute;
use Exception;

/**
 * @implements Loader<User>
 * @noinspection PhpUnused
 */
class LastAccessLoader implements Loader
{
    private EntityManager $entityManager;
    private Acl $acl;

    public function __construct(EntityManager $entityManager, Acl $acl)
    {
        $this->entityManager = $entityManager;
        $this->acl = $acl;
    }

    public function process(Entity $entity, Params $params): void
    {
        if (!$this->acl->checkField($entity->getEntityType(), 'lastAccess')) {
            return;
        }

        $authToken = $this->entityManager
            ->getRDBRepository(AuthToken::ENTITY_TYPE)
            ->select([Attribute::ID, 'lastAccess'])
            ->where([
                'userId' => $entity->getId(),
            ])
            ->order('lastAccess', 'DESC')
            ->findOne();

        $lastAccess = null;

        if ($authToken) {
            $lastAccess = $authToken->get('lastAccess');
        }

        $dt = null;

        if ($lastAccess) {
            try {
                $dt = new DateTime($lastAccess);
            } catch (Exception) {}
        }

        $where = [
            'userId' => $entity->getId(),
            'isDenied' => false,
        ];

        if ($dt) {
            $where['requestTime>'] = $dt->format('U');
        }

        $authLogRecord = $this->entityManager
            ->getRDBRepository(AuthLogRecord::ENTITY_TYPE)
            ->select([Attribute::ID, Field::CREATED_AT])
            ->where($where)
            ->order('requestTime', true)
            ->findOne();

        if ($authLogRecord) {
            $lastAccess = $authLogRecord->get(Field::CREATED_AT);
        }

        $entity->set('lastAccess', $lastAccess);
    }
}
