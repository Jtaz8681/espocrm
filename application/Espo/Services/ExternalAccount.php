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

namespace Espo\Services;

use Espo\Core\Record\ReadResult;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\ReadParams;
use Espo\Entities\ExternalAccount as ExternalAccountEntity;
use Espo\Tools\ExternalAccount\OAuthService;
use Exception;

/**
 * @extends Record<ExternalAccountEntity>
 */
class ExternalAccount extends Record
{
    /**
     * @return bool
     * @deprecated As of v10.0. Use `Espo\Tools\OAuthService`.
     * @todo Fix all usages.
     * @todo Remove in v11.0.
     */
    public function ping(string $integration, string $userId)
    {
        return $this->injectableFactory->create(OAuthService::class)->ping($integration, $userId);
    }

    /**
     * @throws NotFound
     * @throws Error
     * @throws Exception
     * @deprecated As of v10.0. Use `Espo\Tools\OAuthService`.
     * @todo Fix all usages.
     * @todo Remove in v11.0.
     */
    public function authorizationCode(string $integration, string $userId, string $code): void
    {
        $this->injectableFactory->create(OAuthService::class)->authorizationCode($integration, $userId, $code);
    }

    public function read(string $id, ReadParams $params = new ReadParams()): ReadResult
    {
        [, $userId] = explode('__', $id);

        if ($this->user->getId() !== $userId && !$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $entity = $this->entityManager->getRDBRepositoryByClass(ExternalAccountEntity::class)->getById($id);

        if (!$entity) {
            throw new NotFoundSilent();
        }

        [$integration,] = explode('__', $entity->getId());

        $secretAttributeList =
            $this->metadata->get(['integrations', $integration, 'externalAccountSecretAttributeList']) ?? [];

        foreach ($secretAttributeList as $a) {
            $entity->clear($a);
        }

        return new ReadResult($entity);
    }
}
