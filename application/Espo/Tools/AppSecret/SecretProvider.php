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

namespace Espo\Tools\AppSecret;

use Espo\Core\Utils\Crypt;
use Espo\Entities\AppSecret;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;

/**
 * @since 9.0.0
 * @noinspection PhpUnused
 */
class SecretProvider
{
    public function __construct(
        private Crypt $crypt,
        private EntityManager $entityManager,
    ) {}

    /**
     * Get an app secret value.
     *
     * @param string $name A secret name.
     */
    public function get(string $name): ?string
    {
        $secret = $this->entityManager
            ->getRDBRepositoryByClass(AppSecret::class)
            ->where(
                Condition::equal(
                    Expression::binary(Expression::column('name')),
                    $name
                )
            )
            ->findOne();

        if (!$secret) {
            return null;
        }

        return $this->crypt->decrypt($secret->getValue());
    }
}
