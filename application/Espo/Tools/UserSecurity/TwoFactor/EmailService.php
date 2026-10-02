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

namespace Espo\Tools\UserSecurity\TwoFactor;

use Espo\Core\Authentication\TwoFactor\Email\EmailLogin;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Utils\Config;
use Espo\Core\Authentication\TwoFactor\Email\Util;
use Espo\ORM\EntityManager;
use Espo\Entities\User;

class EmailService
{
    public function __construct(
        private Util $util,
        private User $user,
        private EntityManager $entityManager,
        private Config $config
    ) {}

    /**
     * @throws Forbidden
     * @throws NotFound
     * @throws SendingError
     */
    public function sendCode(string $userId, string $emailAddress): void
    {
        if (!$this->user->isAdmin() && $userId !== $this->user->getId()) {
            throw new Forbidden();
        }

        $this->checkAllowed();

        /** @var ?User $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        if (!$user) {
            throw new NotFound();
        }

        $this->util->sendCode($user, $emailAddress);
        $this->util->storeEmailAddress($user, $emailAddress);
    }

    /**
     * @throws Forbidden
     */
    private function checkAllowed(): void
    {
        if (!$this->config->get('auth2FA')) {
            throw new Forbidden("2FA is not enabled.");
        }

        if ($this->user->isPortal() && !$this->config->get('auth2FAInPortal')) {
            throw new Forbidden("2FA is not enabled in portals.");
        }

        $methodList = $this->config->get('auth2FAMethodList') ?? [];

        if (!in_array(EmailLogin::NAME, $methodList)) {
            throw new Forbidden("Email 2FA is not allowed.");
        }
    }
}
