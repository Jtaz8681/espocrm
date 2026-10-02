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

namespace Espo\Core\Acl;

use stdClass;
use RuntimeException;

/**
 * Field data.
 */
class FieldData
{
    /**
     * @var array<string, string>
     */
    private $actionData = [];

    private function __construct() {}

    /**
     * @return never
     */
    public function __get(string $name)
    {
        throw new RuntimeException("Accessing ScopeData properties is not allowed.");
    }

    /**
     * Get a level for an action.
     */
    public function get(string $action): string
    {
        return $this->actionData[$action] ?? Table::LEVEL_NO;
    }

    /**
     * Get a 'read' level.
     */
    public function getRead(): string
    {
        return $this->get(Table::ACTION_READ);
    }

    /**
     * Get an 'edit' level.
     */
    public function getEdit(): string
    {
        return $this->get(Table::ACTION_EDIT);
    }

    /**
     * Create from a raw table value.
     */
    public static function fromRaw(stdClass $raw): self
    {
        $obj = new self();

        $obj->actionData = get_object_vars($raw);

        foreach ($obj->actionData as $item) {
            if (!is_string($item)) {
                throw new RuntimeException("Bad raw scope data.");
            }
        }

        return $obj;
    }
}
