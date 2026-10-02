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

namespace Espo\Tools\Email;

class TestSendData
{
    private string $emailAddress;
    private ?string $type;
    private ?string $id;
    private ?string $userId;

    public function __construct(
        string $emailAddress,
        ?string $type,
        ?string $id,
        ?string $userId
    ) {
        $this->emailAddress = $emailAddress;
        $this->type = $type;
        $this->id = $id;
        $this->userId = $userId;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getType(): ?string
    {
        return $this->type;
    }


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }
}
