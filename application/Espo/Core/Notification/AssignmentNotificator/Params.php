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

namespace Espo\Core\Notification\AssignmentNotificator;

/**
 * Immutable.
 */
class Params
{
    /** @var array<string, mixed> */
    private $options = [];

    private ?string $actionId = null;

    /**
     * Whether an option is set.
     */
    public function hasOption(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * Get an option.
     *
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

    /**
     * Clone with an option.
     *
     * @since 9.0.0
     */
    public function withOption(string $option, mixed $value): self
    {
        $obj = clone $this;
        $obj->options[$option] = $value;

        return $obj;
    }

    /**
     * Create an instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @since 9.2.0
     */
    public function withActionId(?string $actionId): self
    {
        $obj = clone $this;
        $obj->actionId = $actionId;

        return $obj;
    }

    /**
     * @since 9.2.0
     */
    public function getActionId(): ?string
    {
        return $this->actionId;
    }
}
