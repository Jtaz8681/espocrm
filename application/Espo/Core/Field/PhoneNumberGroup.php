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

namespace Espo\Core\Field;

use InvalidArgumentException;

/**
 * A phone number group. Contains a list of phone numbers. One phone number is set as primary.
 * If not empty, then there always should be a primary number. Immutable.
 */
class PhoneNumberGroup
{
    /** @var PhoneNumber[] */
    private $list = [];
    private ?PhoneNumber $primary = null;

    /**
     * @param PhoneNumber[] $list
     * @throws InvalidArgumentException
     */
    public function __construct(array $list = [])
    {
        foreach ($list as $item) {
            $this->list[] = clone $item;
        }

        $this->validateList();

        if (count($this->list) !== 0) {
            $this->primary = $this->list[0];
        }
    }

    public function __clone()
    {
        $newList = [];

        foreach ($this->list as $item) {
            $newList[] = clone $item;
        }

        $this->list = $newList;

        if ($this->primary) {
            $this->primary = clone $this->primary;
        }
    }

    /**
     * Get a primary number as a string. If no primary, then returns null,
     */
    public function getPrimaryNumber(): ?string
    {
        $primary = $this->getPrimary();

        return $primary?->getNumber();

    }

    /**
     * Get a primary phone number.
     */
    public function getPrimary(): ?PhoneNumber
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->primary;
    }

    /**
     * Get a list of all phone numbers.
     *
     * @return PhoneNumber[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * Get a number of phone numbers.
     */
    public function getCount(): int
    {
        return count($this->list);
    }

    /**
     * Get a list of phone numbers w/o a primary.
     *
     * @return PhoneNumber[]
     */
    public function getSecondaryList(): array
    {
        $list = [];

        foreach ($this->list as $item) {
            if ($item === $this->primary) {
                continue;
            }

            $list[] = $item;
        }

        return $list;
    }

    /**
     * Get a list of phone numbers represented as strings.
     *
     * @return string[]
     */
    public function getNumberList(): array
    {
        $list = [];

        foreach ($this->list as $item) {
            $list[] = $item->getNumber();
        }

        return $list;
    }

    /**
     * Get a phone number by number represented as a string.
     */
    public function getByNumber(string $number): ?PhoneNumber
    {
        $index = $this->searchNumberInList($number);

        if ($index === null) {
            return null;
        }

        return $this->list[$index];
    }

    /**
     * Whether the number is in the list.
     */
    public function hasNumber(string $number): bool
    {
        return in_array($number, $this->getNumberList());
    }

    /**
     * Clone with another primary phone number.
     */
    public function withPrimary(PhoneNumber $phoneNumber): self
    {
        $list = $this->list;

        $index = $this->searchNumberInList($phoneNumber->getNumber());

        if ($index !== null) {
            unset($list[$index]);

            $list = array_values($list);
        }

        $newList = array_merge([$phoneNumber], $list);

        return self::create($newList);
    }

    /**
     * Clone with an added phone number list.
     *
     * @param PhoneNumber[] $list
     */
    public function withAddedList(array $list): self
    {
        $newList = $this->list;

        foreach ($list as $item) {
            $index = $this->searchNumberInList($item->getNumber());

            if ($index !== null) {
                $newList[$index] = $item;

                continue;
            }

            $newList[] = $item;
        }

        return self::create($newList);
    }

    /**
     * Clone with an added phone number.
     */
    public function withAdded(PhoneNumber $phoneNumber): self
    {
        return $this->withAddedList([$phoneNumber]);
    }

    /**
     * Clone with removed phone number.
     */
    public function withRemoved(PhoneNumber $phoneNumber): self
    {
        return $this->withRemovedByNumber($phoneNumber->getNumber());
    }

    /**
     * Clone with removed phone number passed by a number.
     */
    public function withRemovedByNumber(string $number): self
    {
        $newList = $this->list;

        $index = $this->searchNumberInList($number);

        if ($index !== null) {
            unset($newList[$index]);

            $newList = array_values($newList);
        }

        return self::create($newList);
    }

    /**
     * Create with an optional phone number list. A first item will be set as primary.
     *
     * @param PhoneNumber[] $list
     * @throws InvalidArgumentException
     */
    public static function create(array $list = []): self
    {
        return new self($list);
    }

    private function searchNumberInList(string $number): ?int
    {
        foreach ($this->getNumberList() as $i => $item) {
            if ($item === $number) {
                return $i;
            }
        }

        return null;
    }

    private function validateList(): void
    {
        $numberList = [];

        foreach ($this->list as $item) {
            if (!$item instanceof PhoneNumber) {
                throw new InvalidArgumentException("Bad item.");
            }

            if (in_array($item->getNumber(), $numberList)) {
                throw new InvalidArgumentException("Number list contains a duplicate.");
            }

            $numberList[] = strtolower($item->getNumber());
        }
    }

    private function isEmpty(): bool
    {
        return count($this->list) === 0;
    }
}
