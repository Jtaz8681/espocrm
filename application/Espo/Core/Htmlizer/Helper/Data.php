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

namespace Espo\Core\Htmlizer\Helper;

use stdClass;
use Closure;

class Data
{
    /**
     * @internal
     * @param array<int, mixed> $argumentList
     * @param array<string, mixed> $rootContext
     */
    public function __construct(
        private string $name,
        private array $argumentList,
        private stdClass $options,
        private mixed $context,
        private array $rootContext,
        private ?Closure $func,
        private ?Closure $inverseFunc,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * A scope context.
     *
     * @return mixed
     */
    public function getContext(): mixed
    {
        return $this->context;
    }

    /**
     * A root context.
     *
     * @return array<string, mixed>
     */
    public function getRootContext(): array
    {
        return $this->rootContext;
    }

    /**
     * Specified helper options (parameters).
     *
     * @return stdClass
     */
    public function getOptions(): stdClass
    {
        return $this->options;
    }

    /**
     * @return array<int, mixed>
     */
    public function getArgumentList(): array
    {
        return $this->argumentList;
    }

    /**
     * Has a specified helper option (parameter).
     */
    public function hasOption(string $name): bool
    {
        return property_exists($this->options, $name);
    }

    /**
     * Get the value of a specified helper option (parameter).
     */
    public function getOption(string $name): mixed
    {
        return $this->options->$name ?? null;
    }

    public function getFunction(): ?Closure
    {
        return $this->func;
    }

    /** @noinspection PhpUnused */
    public function getInverseFunction(): ?Closure
    {
        return $this->inverseFunc;
    }
}
