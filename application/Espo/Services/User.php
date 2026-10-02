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

use Espo\Core\Di\LogAware;
use Espo\Core\Di\LogSetter;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Record\CreateResult;
use Espo\Core\Record\UpdateResult;
use Espo\Entities\User as UserEntity;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\UpdateParams;
use Espo\Core\Utils\PasswordHash;
use Espo\ORM\Entity;
use Espo\Tools\UserSecurity\Password\Checker as PasswordChecker;
use Espo\Tools\UserSecurity\Password\Generator as PasswordGenerator;
use Espo\Tools\UserSecurity\Password\Sender as PasswordSender;
use Espo\Tools\UserSecurity\Password\Service as PasswordService;
use SensitiveParameter;
use stdClass;
use Exception;

/**
 * @extends Record<UserEntity>
 */
class User extends Record implements LogAware
{
    use LogSetter;

    private function hashPassword(#[SensitiveParameter] string $password): string
    {
        $passwordHash = $this->injectableFactory->create(PasswordHash::class);

        return $passwordHash->hash($password);
    }

    /**
     * @throws BadRequest
     */
    private function fetchPassword(#[SensitiveParameter] stdClass $data): ?string
    {
        $password = $data->password ?? null;

        if ($password === '') {
            $password = null;
        }

        if ($password !== null && !is_string($password)) {
            throw new BadRequest("Bad password value.");
        }

        return $password;
    }

    public function create(stdClass $data, CreateParams $params = new CreateParams()): CreateResult
    {
        $newPassword = $this->fetchPassword($data);

        $passwordSpecified = $newPassword !== null;

        if (
            $newPassword !== null &&
            !$this->createPasswordChecker()->checkStrength($newPassword)
        ) {
            throw new Forbidden("Password is weak.");
        }

        if (!$newPassword) {
            // Generate a password as authentication implementations may require user records
            // to have passwords for auth token mechanism functioning.
            $newPassword = $this->createPasswordGenerator()->generate();
        }

        $data->password = $this->hashPassword($newPassword);

        $result = parent::create($data, $params);

        $user = $result->getEntity();


        $sendAccessInfo = !empty($data->sendAccessInfo);

        if (!$sendAccessInfo || !$user->isActive() || $user->isApi()) {
            return $result;
        }

        try {
            if ($passwordSpecified) {
                $this->sendPassword($user, $newPassword);

                return $result;
            }

            $this->getPasswordService()->sendAccessInfoForNewUser($user);
        } catch (Exception $e) {
            $this->log->error("Could not send user access info. " . $e->getMessage());
        }

        return $result;
    }

    public function update(string $id, stdClass $data, UpdateParams $params = new UpdateParams()): UpdateResult
    {
        $newPassword = null;

        if (property_exists($data, 'password')) {
            $newPassword = $data->password;

            if (!$this->createPasswordChecker()->checkStrength($newPassword)) {
                throw new Forbidden("Password is weak.");
            }

            $data->password = $this->hashPassword($data->password);
        }

        if ($id === $this->user->getId()) {
            unset($data->isActive);
            unset($data->isPortalUser);
            unset($data->type);
        }

        $result = parent::update($id, $data, $params);

        $user = $result->getEntity();

        if (!is_null($newPassword)) {
            try {
                if ($user->isActive() && !empty($data->sendAccessInfo)) {
                    $this->sendPassword($user, $newPassword);
                }
            } catch (Exception) {}
        }

        return $result;
    }

    private function getPasswordService(): PasswordService
    {
        return $this->injectableFactory->create(PasswordService::class);
    }

    /**
     * @throws SendingError
     */
    private function sendPassword(UserEntity $user, string $password): void
    {
        $this->injectableFactory
            ->create(PasswordSender::class)
            ->sendPassword($user, $password);
    }

    private function createPasswordChecker(): PasswordChecker
    {
        return $this->injectableFactory->create(PasswordChecker::class);
    }

    private function createPasswordGenerator(): PasswordGenerator
    {
        return $this->injectableFactory->create(PasswordGenerator::class);
    }
}
