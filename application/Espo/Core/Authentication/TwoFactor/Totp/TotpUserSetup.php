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

namespace Espo\Core\Authentication\TwoFactor\Totp;

use Espo\Core\Exceptions\BadRequest;
use Espo\Entities\UserData;
use Espo\Entities\User;
use Espo\Repositories\UserData as UserDataRepository;
use Espo\ORM\EntityManager;
use Espo\Core\Authentication\TwoFactor\UserSetup;
use Espo\Core\Utils\Config;

use RuntimeException;
use SensitiveParameter;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class TotpUserSetup implements UserSetup
{
    public function __construct(
        private Util $totp,
        private Config $config,
        private EntityManager $entityManager
    ) {}

    public function getData(User $user): stdClass
    {
        $userName = $user->get('userName');

        $secret = $this->totp->createSecret();

        $label = rawurlencode($this->config->get('applicationName')) . ':' . rawurlencode($userName);

        $this->storeSecret($user, $secret);

        return (object) [
            'auth2FATotpSecret' => $secret,
            'label' => $label,
        ];
    }

    public function verifyData(User $user, stdClass $payloadData): bool
    {
        $code = $payloadData->code ?? null;

        if ($code === null) {
            throw new BadRequest("No code.");
        }

        $codeModified = str_replace(' ', '', trim($code));

        if (!$codeModified) {
            return false;
        }

        $userData = $this->getUserDataRepository()->getByUserId($user->getId());

        if (!$userData) {
            throw new RuntimeException("User not found.");
        }

        $secret = $userData->get('auth2FATotpSecret');

        return $this->totp->verifyCode($secret, $codeModified);
    }

    private function storeSecret(User $user, #[SensitiveParameter] string $secret): void
    {
        $userData = $this->getUserDataRepository()->getByUserId($user->getId());

        if (!$userData) {
            throw new RuntimeException();
        }

        $userData->set('auth2FATotpSecret', $secret);

        $this->entityManager->saveEntity($userData);
    }

    private function getUserDataRepository(): UserDataRepository
    {
        /** @var UserDataRepository $repository */
        $repository = $this->entityManager->getRepository(UserData::ENTITY_TYPE);

        return $repository;
    }
}
