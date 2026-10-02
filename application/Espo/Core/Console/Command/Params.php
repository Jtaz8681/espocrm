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

namespace Espo\Core\Console\Command;

use Espo\Core\Utils\Util;

/**
 * Command parameters.
 *
 * Immutable.
 */
class Params
{
    /** @var array<string, string> */
    private $options;
    /** @var string[] */
    private $flagList;
    /** @var string[] */
    private $argumentList;

    /**
     * @param array<string, string>|null $options
     * @param string[]|null $flagList
     * @param string[]|null $argumentList
     */
    public function __construct(?array $options, ?array $flagList, ?array $argumentList)
    {
        $this->options = $options ?? [];
        $this->flagList = $flagList ?? [];
        $this->argumentList = $argumentList ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return string[]
     */
    public function getFlagList(): array
    {
        return $this->flagList;
    }

    /**
     * @return string[]
     */
    public function getArgumentList(): array
    {
        return $this->argumentList;
    }

    /**
     * Has an option.
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Get an option.
     */
    public function getOption(string $name): ?string
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Has a flag.
     */
    public function hasFlag(string $name): bool
    {
        return in_array($name, $this->flagList);
    }

    /**
     * Get an argument by index.
     */
    public function getArgument(int $index): ?string
    {
        return $this->argumentList[$index] ?? null;
    }

    /**
     * @param array<int, string> $args
     */
    public static function fromArgs(array $args): self
    {
        $argumentList = [];
        $options = [];
        $flagList = [];

        foreach ($args as $i => $item) {
            if (str_starts_with($item, '--') && strpos($item, '=') > 2) {
                [$name, $value] = explode('=', substr($item, 2));

                $name = Util::hyphenToCamelCase($name);

                $options[$name] = $value;
            } else if (str_starts_with($item, '--')) {
                $flagList[] = Util::hyphenToCamelCase(substr($item, 2));
            } else if (str_starts_with($item, '-')) {
                $flagList[] = substr($item, 1);
            } else if ($i > 0) {
                $argumentList[] = $item;
            }
        }

        return new self($options, $flagList, $argumentList);
    }
}
