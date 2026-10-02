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

namespace Espo\Core\Authentication\Oidc;

class PkceUtil
{
    private const string CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
    private const int CODE_LENGTH = 64;

    public static function generateCodeVerifier(): string
    {
        $output = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $output .= self::CHARACTERS[random_int(0, strlen(self::CHARACTERS) - 1)];
        }

        return $output;
    }

    public static function hashAndEncodeCodeVerifier(string $codeVerifier): string
    {
        $code = hash('sha256', $codeVerifier, true);

        return rtrim(strtr(base64_encode($code), '+/', '-_'), '=');
    }
}
