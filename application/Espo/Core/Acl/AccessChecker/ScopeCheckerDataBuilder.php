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

namespace Espo\Core\Acl\AccessChecker;

use Closure;

/**
 * Builds scope checker data.
 */
class ScopeCheckerDataBuilder
{
    private Closure $isOwnChecker;
    private Closure $inTeamChecker;
    private Closure $isSharedChecker;

    public function __construct()
    {
        $this->isOwnChecker = fn(): bool => false;
        $this->inTeamChecker = fn(): bool => false;
        $this->isSharedChecker = fn(): bool => false;
    }

    public function setIsOwn(bool $value): self
    {
        if ($value) {
            $this->isOwnChecker = function (): bool {
                return true;
            };

            return $this;
        }

        $this->isOwnChecker = function (): bool {
            return false;
        };

        return $this;
    }

    public function setInTeam(bool $value): self
    {
        if ($value) {
            $this->inTeamChecker = function (): bool {
                return true;
            };

            return $this;
        }

        $this->inTeamChecker = function (): bool {
            return false;
        };

        return $this;
    }

    public function setIsShared(bool $value): self
    {
        if ($value) {
            $this->isSharedChecker = fn(): bool => true;

            return $this;
        }

        $this->isSharedChecker = fn(): bool => false;

        return $this;
    }

    /**
     * @param Closure(): bool $checker
     */
    public function setIsOwnChecker(Closure $checker): self
    {
        $this->isOwnChecker = $checker;

        return $this;
    }

    /**
     * @param Closure(): bool $checker
     */
    public function setInTeamChecker(Closure $checker): self
    {
        $this->inTeamChecker = $checker;

        return $this;
    }

    /**
     * @param Closure(): bool $checker
     */
    public function setIsSharedChecker(Closure $checker): self
    {
        $this->isSharedChecker = $checker;

        return $this;
    }

    public function build(): ScopeCheckerData
    {
        return new ScopeCheckerData($this->isOwnChecker, $this->inTeamChecker, $this->isSharedChecker);
    }
}
