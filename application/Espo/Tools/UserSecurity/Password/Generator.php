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

use Espo\Core\Utils\Util;

/**
 * A password generator.
 *
 * @todo Use an interface with binding.
 */
class Generator
{
    public function __construct(
        private ConfigProvider $configProvider,
    ) {}

    /**
     * Generate a password.
     */
    public function generate(): string
    {
        $length = $this->configProvider->getStrengthLength();
        $letterCount = $this->configProvider->getStrengthLetterCount();
        $numberCount = $this->configProvider->getStrengthNumberCount();
        $specialCharacterCount = $this->configProvider->getStrengthSpecialCharacterCount() ?? 0;

        $generateLength = $this->configProvider->getGenerateLength() ?? 10;
        $generateLetterCount = $this->configProvider->getGenerateLetterCount() ?? 4;
        $generateNumberCount = $this->configProvider->getGenerateNumberCount() ?? 2;

        $length = is_null($length) ? $generateLength : $length;
        $letterCount = is_null($letterCount) ? $generateLetterCount : $letterCount;
        $numberCount = is_null($numberCount) ? $generateNumberCount : $numberCount;

        if ($length < $generateLength) {
            $length = $generateLength;
        }

        if ($letterCount < $generateLetterCount) {
            $letterCount = $generateLetterCount;
        }

        if ($numberCount < $generateNumberCount) {
            $numberCount = $generateNumberCount;
        }

        return Util::generatePassword($length, $letterCount, $numberCount, true, $specialCharacterCount);
    }
}
