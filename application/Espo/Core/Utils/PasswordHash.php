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

namespace Espo\Core\Utils;

use RuntimeException;
use SensitiveParameter;

use const PASSWORD_BCRYPT;

class PasswordHash
{
    /**
     * Legacy.
     */
    private string $saltFormat = '$6${0}$';

    public function __construct(private Config $config)
    {}

    /**
     * Hash a password.
     */
    public function hash(#[SensitiveParameter] string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify a password against a hash.
     */
    public function verify(
        #[SensitiveParameter] string $password,
        #[SensitiveParameter] string $hash
    ): bool {

        if (password_verify($password, $hash)) {
            return true;
        }

        return $this->legacyVerify($password, $hash);
    }

    private function legacyVerify(
        #[SensitiveParameter] string $password,
        #[SensitiveParameter] string $hash
    ): bool {

        if (!$this->config->get('passwordSalt')) {
            return false;
        }

        return $this->legacyHash($password) === $hash;
    }

    private function legacyHash(#[SensitiveParameter] string $password): string
    {
        $salt = $this->getSalt();

        $hash = crypt(md5($password), $salt);

        return str_replace($salt, '', $hash);
    }

    private function getSalt(): string
    {
        $salt = $this->config->get('passwordSalt');

        if (!isset($salt)) {
            throw new RuntimeException('Option "passwordSalt" does not exist in config.php');
        }

        return $this->normalizeSalt($salt);
    }

    private function normalizeSalt(string $salt): string
    {
        return str_replace("{0}", $salt, $this->saltFormat);
    }
}
