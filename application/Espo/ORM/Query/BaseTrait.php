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

namespace Espo\ORM\Query;

trait BaseTrait
{
    /**
     * @var array<string, mixed>
     */
    private $params = [];

    /**
     * Get parameters in RAW format.
     *
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->params;
    }

    /**
     * Create from RAW params.
     *
     * @param array<string, mixed> $params
     */
    public static function fromRaw(array $params): self
    {
        $obj = new self();

        $obj->validateRawParams($params);

        $obj->params = $params;

        return $obj;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateRawParams(array $params): void
    {}
}
