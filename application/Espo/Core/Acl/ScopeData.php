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
use InvalidArgumentException;
use RuntimeException;

/**
 * Scope data.
 */
class ScopeData
{
    /** @var stdClass|bool */
    private $raw;
    /** @var array<string, string> */
    private $actionData = [];
    private bool $isBoolean = false;

    private function __construct() {}

    /**
     * @return never
     */
    public function __get(string $name)
    {
        throw new RuntimeException("Accessing ScopeData properties is not allowed.");
    }

    /**
     * Is of boolean type.
     */
    public function isBoolean(): bool
    {
        return $this->isBoolean;
    }

    /**
     * Is true.
     */
    public function isTrue(): bool
    {
        if (!$this->isBoolean) {
            return false;
        }

        return $this->raw === true;
    }

    /**
     * Is false.
     */
    public function isFalse(): bool
    {
        if (!$this->isBoolean) {
            return false;
        }

        return $this->raw === false;
    }

    /**
     * Has any level other than 'no'.
     */
    public function hasNotNo(): bool
    {
        foreach ($this->actionData as $level) {
            if ($level !== Table::LEVEL_NO) {
                return true;
            }
        }

        return false;
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
     * Get a 'stream' level.
     */
    public function getStream(): string
    {
        return $this->get(Table::ACTION_STREAM);
    }

    /**
     * Get a 'create' level.
     */
    public function getCreate(): string
    {
        return $this->get(Table::ACTION_CREATE);
    }

    /**
     * Get an 'edit' level.
     */
    public function getEdit(): string
    {
        return $this->get(Table::ACTION_EDIT);
    }

    /**
     * Get a 'delete' level.
     */
    public function getDelete(): string
    {
        return $this->get(Table::ACTION_DELETE);
    }

    /**
     * Create from a raw table value.
     *
     * @param stdClass|bool $raw
     * @return self
     */
    public static function fromRaw($raw): self
    {
        /** @var mixed $raw */

        $obj = new self();

        if ($raw instanceof stdClass) {
            $obj->isBoolean = false;

            $obj->actionData = get_object_vars($raw);

            foreach ($obj->actionData as $item) {
                if (!is_string($item)) {
                    throw new RuntimeException("Bad raw scope data.");
                }
            }
        } else if (is_bool($raw)) {
            $obj->isBoolean = true;
        } else {
            throw new InvalidArgumentException();
        }

        $obj->raw = $raw;

        return $obj;
    }
}
