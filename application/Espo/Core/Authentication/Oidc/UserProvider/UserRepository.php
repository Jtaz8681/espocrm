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

namespace Espo\Core\Authentication\Oidc\UserProvider;

use Espo\Core\FieldProcessing\EmailAddress\Saver as EmailAddressSaver;
use Espo\Core\FieldProcessing\PhoneNumber\Saver as PhoneNumberSaver;
use Espo\Core\FieldProcessing\Relation\LinkMultipleSaver;
use Espo\Core\FieldProcessing\Saver\Params as SaverParams;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

class UserRepository
{
    public function __construct(
        private EntityManager $entityManager,
        private LinkMultipleSaver $linkMultipleSaver,
        private EmailAddressSaver $emailAddressSaver,
        private PhoneNumberSaver $phoneNumberSaver
    ) {}

    public function getNew(): User
    {
        return $this->entityManager->getRDBRepositoryByClass(User::class)->getNew();
    }

    public function save(User $user): void
    {
        $this->entityManager->saveEntity($user, [
            // Prevent `user` service being loaded by hooks.
            SaveOption::SKIP_HOOKS => true,
            SaveOption::KEEP_NEW => true,
            SaveOption::KEEP_DIRTY => true,
        ]);

        $saverParams = SaverParams::create()->withRawOptions(['skipLinkMultipleHooks' => true]);

        $this->linkMultipleSaver->process($user, Field::TEAMS, $saverParams);
        $this->linkMultipleSaver->process($user, 'portals', $saverParams);
        $this->linkMultipleSaver->process($user, 'portalRoles', $saverParams);
        $this->emailAddressSaver->process($user, $saverParams);
        $this->phoneNumberSaver->process($user, $saverParams);

        $user->setAsNotNew();
        $user->updateFetchedValues();

        $this->entityManager->refreshEntity($user);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where(['userName' => $username])
            ->findOne();
    }
}
