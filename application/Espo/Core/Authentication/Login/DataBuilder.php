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

namespace Espo\Core\Authentication\Login;

use Espo\Core\Authentication\AuthToken\AuthToken;
use SensitiveParameter;

class DataBuilder
{
    private ?string $username = null;
    private ?string $password = null;
    private ?AuthToken $authToken = null;

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(#[SensitiveParameter] ?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setAuthToken(?AuthToken $authToken): self
    {
        $this->authToken = $authToken;

        return $this;
    }

    public function build(): Data
    {
        return new Data($this->username, $this->password, $this->authToken);
    }
}
