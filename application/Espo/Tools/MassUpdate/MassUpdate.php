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

namespace Espo\Tools\MassUpdate;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\Result;
use Espo\Core\MassAction\MassActionFactory;

use Espo\Core\Utils\SystemUser;
use Espo\ORM\EntityManager;
use Espo\Entities\User;

use RuntimeException;

/**
 * Entry point for the mass-update tool.
 */
class MassUpdate
{
    private const ACTION = 'massUpdate';

    public function __construct(
        private MassActionFactory $massActionFactory,
        private EntityManager $entityManager
    ) {}

    /**
     * Process.
     *
     * @param ?User $user Under what user to perform mass-update. If not specified, the system user will be used.
     * Access control is applied for the user.
     * @throws NotFound
     * @throws Forbidden
     * @throws BadRequest
     */
    public function process(Params $params, Data $data, ?User $user = null): Result
    {
        $entityType = $params->getEntityType();

        if (!$user) {
            $user = $this->entityManager
                ->getRDBRepositoryByClass(User::class)
                ->where(['userName' => SystemUser::NAME])
                ->findOne();
        }

        if (!$user) {
            throw new RuntimeException("No user.");
        }

        $action = $this->massActionFactory->createForUser(self::ACTION, $entityType, $user);

        return $action->process($params, $data->toMassActionData());
    }
}
