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

namespace Espo\Tools\ExternalAccount;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Core\Utils\Metadata;
use Espo\Entities\ExternalAccount;
use Espo\Entities\Integration;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use stdClass;

/**
 * @since 10.0.0
 */
class Service
{
    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
        private Acl $acl,
        private User $user,
        private ApplicationConfig $config,
        private OAuthService $oAuthService,
    ) {}

    /**
     * @internal
     */
    public function getList(): stdClass
    {
        $integrations = $this->entityManager
            ->getRDBRepositoryByClass(Integration::class)
            ->find();

        $list = [];

        foreach ($integrations as $entity) {
            if (
                !$entity->isEnabled() ||
                !$this->metadata->get("integrations.{$entity->getId()}.allowUserAccounts")
            ) {
                continue;
            }

            $id = $entity->getId();

            $userAccountAclScope = $this->metadata->get(['integrations', $id, 'userAccountAclScope']);

            if ($userAccountAclScope && !$this->acl->checkScope($userAccountAclScope)) {
                continue;
            }

            $list[] = [
                'id' => $id,
            ];
        }

        return (object) [
            'list' => $list
        ];
    }

    /**
     * @throws Forbidden
     * @internal
     */
    public function getActionGetOAuth2Info(string $id): ?stdClass
    {
        [$integration, $userId] = explode('__', $id);

        if ($this->user->getId() != $userId && !$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $entity = $this->entityManager->getRDBRepositoryByClass(Integration::class)->getById($integration);

        if (!$entity) {
            return null;
        }

        return (object) [
            'clientId' => $entity->get('clientId'),
            'redirectUri' => $this->config->getSiteUrl() . '?entryPoint=oauthCallback',
            'isConnected' => $this->oAuthService->ping($integration, $userId)
        ];
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function update(string $id, stdClass $data): stdClass
    {
        [, $userId] = explode('__', $id);

        if ($this->user->getId() !== $userId && !$this->user->isAdmin()) {
            throw new Forbidden();
        }

        if (isset($data->enabled) && !$data->enabled) {
            $data->data = null;
        }

        $entity = $this->entityManager->getRDBRepositoryByClass(ExternalAccount::class)->getById($id);

        if (!$entity) {
            throw new NotFound();
        }

        $entity->setMultiple($data);

        $this->entityManager->saveEntity($entity);

        return $entity->getValueMap();
    }

    /**
     * @internal
     * @throws Forbidden
     * @throws NotFound
     * @throws Error
     */
    public function authorizationCode(string $id, string $code): void
    {
        [$integration, $userId] = explode('__', $id);

        if ($this->user->getId() !== $userId && !$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $this->oAuthService->authorizationCode($integration, $userId, $code);
    }
}
