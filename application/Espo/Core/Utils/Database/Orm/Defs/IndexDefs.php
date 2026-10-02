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

namespace Espo\Core\Utils\Database\Orm\Defs;

use Espo\Core\Utils\Util;
use Espo\ORM\Defs\Params\IndexParam;

/**
 * Immutable.
 */
class IndexDefs
{
    /** @var array<string, mixed> */
    private array $params = [];

    private function __construct(private string $name) {}

    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * Get a relation name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Whether a parameter is set.
     */
    public function hasParam(string $name): bool
    {
        return array_key_exists($name, $this->params);
    }

    /**
     * Get a parameter value.
     */
    public function getParam(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Clone with a parameter.
     */
    public function withParam(string $name, mixed $value): self
    {
        $obj = clone $this;
        $obj->params[$name] = $value;

        return $obj;
    }

    /**
     * Clone without a parameter.
     */
    public function withoutParam(string $name): self
    {
        $obj = clone $this;
        unset($obj->params[$name]);

        return $obj;
    }

    public function withUnique(): self
    {
        $obj = clone $this;
        $obj->params[IndexParam::TYPE] = 'unique';

        return $obj;
    }

    public function withoutUnique(): self
    {
        $obj = clone $this;
        unset($obj->params[IndexParam::TYPE]);

        return $obj;
    }

    public function withFlag(string $flag): self
    {
        $obj = clone $this;

        $flags = $obj->params[IndexParam::FLAGS] ?? [];

        if (!in_array($flag, $flags)) {
            $flags[] = $flag;
        }

        $obj->params[IndexParam::FLAGS] = $flags;

        return $obj;
    }

    public function withoutFlag(string $flag): self
    {
        $obj = clone $this;

        $flags = $obj->params[IndexParam::FLAGS] ?? [];

        $index = array_search($flag, $flags, true);

        if ($index !== -1) {
            unset($flags[$index]);
            $flags = array_values($flags);
        }

        $obj->params[IndexParam::FLAGS] = $flags;

        if ($flags === []) {
            unset($obj->params[IndexParam::FLAGS]);
        }

        return $obj;
    }

    /**
     * Clone with parameters merged.
     *
     * @param array<string, mixed> $params
     */
    public function withParamsMerged(array $params): self
    {
        $obj = clone $this;

        /** @var array<string, mixed> $params */
        $params = Util::merge($this->params, $params);

        $obj->params = $params;

        return $obj;
    }

    /**
     * To an associative array.
     *
     * @return array<string, mixed>
     */
    public function toAssoc(): array
    {
        return $this->params;
    }
}
