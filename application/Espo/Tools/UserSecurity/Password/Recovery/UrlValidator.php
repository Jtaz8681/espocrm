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

namespace Espo\Tools\UserSecurity\Password\Recovery;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Config;
use Espo\Entities\Portal;
use Espo\ORM\EntityManager;

class UrlValidator
{
    public function __construct(
        private Config $config,
        private EntityManager $entityManager
    ) {}

    /**
     * @throws Forbidden
     */
    public function validate(string $url): void
    {
        $siteUrl = rtrim($this->config->get('siteUrl') ?? '', '/');

        if (UrlValidatorUtil::validate($url, $siteUrl)) {
            return;
        }

        /** @var iterable<Portal> $portals */
        $portals = $this->entityManager
            ->getRDBRepositoryByClass(Portal::class)
            ->find();

        foreach ($portals as $portal) {
            $siteUrl = rtrim($portal->getUrl() ?? '', '/');

            if (UrlValidatorUtil::validate($url, $siteUrl)) {
                return;
            }
        }

        throw new Forbidden("URL does not match Site URL.");
    }
}
