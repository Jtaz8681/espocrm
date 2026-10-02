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

namespace Espo\Core\Htmlizer;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\DateTime\DateTimeFactory;
use Espo\Core\AclManager;
use Espo\Entities\User;

/**
 * Not for direct use. Use `TemplateRenderer`.
 * @internal
 */
class HtmlizerFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private DateTimeFactory $dateTimeFactory,
        private AclManager $aclManager
    ) {}

    public function create(bool $skipAcl = false, ?string $timeZone = null): Htmlizer
    {
        $with = [];

        if ($skipAcl) {
            $with['acl'] = null;
        }

        if ($timeZone) {
            $with['dateTime'] = $this->dateTimeFactory->createWithTimeZone($timeZone);
        }

        return $this->injectableFactory->createWith(Htmlizer::class, $with);
    }

    public function createNoAcl(): Htmlizer
    {
        return $this->create(true);
    }

    public function createForUser(User $user, ?CreateForUserParams $params = null): Htmlizer
    {
        if (!$params) {
            $params = new CreateForUserParams();
            $params->useUserTimezone = true;
            $params->applyAcl = true;
        }

        $deps = [];

        if ($params->useUserTimezone) {
            $deps['dateTime'] = $this->dateTimeFactory->createWithUserTimeZone($user);
        }

        if ($params->applyAcl) {
            $deps['acl'] = $this->aclManager->createUserAcl($user);
            $deps['user'] = $user;
        }

        return $this->injectableFactory->createWith(Htmlizer::class, $deps);
    }
}
