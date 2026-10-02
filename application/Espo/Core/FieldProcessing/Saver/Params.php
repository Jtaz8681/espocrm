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

namespace Espo\Core\FieldProcessing\Saver;

/**
 * Immutable.
 */
class Params
{
    /**
     * @var array<string, mixed>
     */
    private $options = [];

    public function __construct()
    {
    }

    public function hasOption(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * @return mixed
     */
    public function getOption(string $option)
    {
        return $this->options[$option] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRawOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
      */
    public function withRawOptions(array $options): self
    {
        $obj = clone $this;

        $obj->options = $options;

        return $obj;
    }

    public static function create(): self
    {
        return new self();
    }
}
