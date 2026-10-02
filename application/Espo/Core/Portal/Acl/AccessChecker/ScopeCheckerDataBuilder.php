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

namespace Espo\Core\Portal\Acl\AccessChecker;

use Closure;

/**
 * Builds scope checker data.
 */
class ScopeCheckerDataBuilder
{
    private Closure $isOwnChecker;
    private Closure $inAccountChecker;
    private Closure $inContactChecker;

    public function __construct()
    {
        $this->isOwnChecker = function (): bool {
            return false;
        };

        $this->inAccountChecker = function (): bool {
            return false;
        };

        $this->inContactChecker = function (): bool {
            return false;
        };
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

    public function setInAccount(bool $value): self
    {
        if ($value) {
            $this->inAccountChecker = function (): bool {
                return true;
            };

            return $this;
        }

        $this->inAccountChecker = function (): bool {
            return false;
        };

        return $this;
    }

    public function setInContact(bool $value): self
    {
        if ($value) {
            $this->inContactChecker = function (): bool {
                return true;
            };

            return $this;
        }

        $this->inContactChecker = function (): bool {
            return false;
        };

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
    public function setInAccountChecker(Closure $checker): self
    {
        $this->inAccountChecker = $checker;

        return $this;
    }

    /**
     * @param Closure(): bool $checker
     */
    public function setInContactChecker(Closure $checker): self
    {
        $this->inContactChecker = $checker;

        return $this;
    }

    public function build(): ScopeCheckerData
    {
        return new ScopeCheckerData($this->isOwnChecker, $this->inAccountChecker, $this->inContactChecker);
    }
}
