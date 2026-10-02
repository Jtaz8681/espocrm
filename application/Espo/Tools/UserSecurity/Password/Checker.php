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

namespace Espo\Tools\UserSecurity\Password;

use SensitiveParameter;

class Checker
{
    private const SPECIAL_CHARACTERS = "'-!\"#$%&()*,./:;?@[]^_`{|}~+<=>";

    public function __construct(
        private ConfigProvider $configProvider,
    ) {}

    public function checkStrength(#[SensitiveParameter] string $password): bool
    {
        $minLength = $this->configProvider->getStrengthLength();

        if ($minLength) {
            if (mb_strlen($password) < $minLength) {
                return false;
            }
        }

        $requiredLetterCount = $this->configProvider->getStrengthLetterCount();

        if ($requiredLetterCount) {
            $letterCount = 0;

            foreach (str_split($password) as $c) {
                if (ctype_alpha($c)) {
                    $letterCount++;
                }
            }

            if ($letterCount < $requiredLetterCount) {
                return false;
            }
        }

        $requiredNumberCount = $this->configProvider->getStrengthNumberCount();

        if ($requiredNumberCount) {
            $numberCount = 0;

            foreach (str_split($password) as $c) {
                if (is_numeric($c)) {
                    $numberCount++;
                }
            }

            if ($numberCount < $requiredNumberCount) {
                return false;
            }
        }

        $bothCases = $this->configProvider->getStrengthBothCases();

        if ($bothCases) {
            $ucCount = 0;
            $lcCount = 0;

            foreach (str_split($password) as $c) {
                if (ctype_alpha($c) && $c === mb_strtoupper($c)) {
                    $ucCount++;
                }

                if (ctype_alpha($c) && $c === mb_strtolower($c)) {
                    $lcCount++;
                }
            }
            if (!$ucCount || !$lcCount) {
                return false;
            }
        }

        $specialCharacterCount = $this->configProvider->getStrengthSpecialCharacterCount();

        if ($specialCharacterCount) {
            $realSpecialCharacterCount = 0;

            foreach (str_split($password) as $c) {
                if (str_contains(self::SPECIAL_CHARACTERS, $c)) {
                    $realSpecialCharacterCount++;
                }
            }

            if ($realSpecialCharacterCount < $specialCharacterCount) {
                return false;
            }
        }

        return true;
    }
}
