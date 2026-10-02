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

namespace Espo\Tools\PopupNotification;

use stdClass;

class Item
{
    private ?string $id;
    private stdClass $data;

    /**
     * @param ?string $id An ID.
     * @param stdClass $data Data to pass to a front-end handler.
     */
    public function __construct(
        ?string $id,
        stdClass $data
    ) {
        $this->id = $id;
        $this->data = $data;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getData(): stdClass
    {
        return $this->data;
    }
}
