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

namespace Espo\Core\Formula;

use BadMethodCallException;
use OutOfBoundsException;
use Iterator;
use Countable;
use ArrayAccess;
use SeekableIterator;

/**
 * A list of function arguments.
 *
 * @implements ArrayAccess<int, Argument>
 * @implements Iterator<Argument>
 * @implements SeekableIterator<int, Argument>
 */
class ArgumentList implements Evaluatable, Iterator, Countable, ArrayAccess, SeekableIterator
{
    private int $position = 0;

    /**
     * @param array<int, mixed> $dataList
     */
    public function __construct(private array $dataList)
    {}

    private function getLastValidKey(): int
    {
        $keys = array_keys($this->dataList);

        $i = end($keys);

        if ($i === false) {
            return -1;
        }

        while ($i > 0) {
            if (array_key_exists($i, $this->dataList)) {
                break;
            }

            $i--;
        }

        return $i;
    }

    public function rewind(): void
    {
        $this->position = 0;

        while (!$this->valid() && $this->position <= $this->getLastValidKey()) {
            $this->position ++;
        }
    }

    private function getArgumentByIndex(int $index): Argument
    {
        return new Argument($this->dataList[$index]);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->getArgumentByIndex($this->position);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    public function next(): void
    {
        do {
            $this->position ++;
            $next = false;

            if (
                !$this->valid() &&
                $this->position <= $this->getLastValidKey()
            ) {
                $next = true;
            }
        } while ($next);
    }

    public function valid(): bool
    {
        return array_key_exists($this->position, $this->dataList);
    }

    /**
     * @param mixed $offset
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->dataList);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return $this->getArgumentByIndex($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Setting is not allowed.');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Unsetting is not allowed.');
    }

    public function count(): int
    {
        return count($this->dataList);
    }

    /**
     * @param int $offset
     */
    public function seek($offset): void
    {
        $this->position = $offset;

        if (!$this->valid()) {
            throw new OutOfBoundsException("Invalid seek offset ($offset).");
        }
    }
}
