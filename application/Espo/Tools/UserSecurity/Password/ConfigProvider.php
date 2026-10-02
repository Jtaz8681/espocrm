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

use Espo\Core\Utils\Config;

class ConfigProvider
{

    public function __construct(
        private Config $config,
    ) {}

    public function getStrengthLength(): ?int
    {
        return $this->config->get('passwordStrengthLength');
    }

    public function getStrengthLetterCount(): ?int
    {
        return $this->config->get('passwordStrengthLetterCount');
    }

    public function getStrengthNumberCount(): ?int
    {
        return $this->config->get('passwordStrengthNumberCount');
    }

    public function getStrengthSpecialCharacterCount(): ?int
    {
        return $this->config->get('passwordStrengthSpecialCharacterCount');
    }

    public function getStrengthBothCases(): bool
    {
        return (bool) $this->config->get('passwordStrengthBothCases');
    }

    public function getGenerateLength(): ?int
    {
        return $this->config->get('passwordGenerateLength');
    }
    public function getGenerateLetterCount(): ?int
    {
        return $this->config->get('passwordGenerateLetterCount');
    }

    public function getGenerateNumberCount(): ?int
    {
        return $this->config->get('passwordGenerateNumberCount');
    }
}
