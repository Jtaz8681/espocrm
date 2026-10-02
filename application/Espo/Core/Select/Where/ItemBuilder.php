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

namespace Espo\Core\Select\Where;

use Espo\Core\Select\Where\Item\Data;

/**
 * A where-item builder.
 */
class ItemBuilder
{
    private ?string $type = null;
    private ?string $attribute = null;
    /** @var mixed */
    private $value = null;
    private ?Data $data = null;

    public static function create(): self
    {
        return new self();
    }

    /**
     * Set a type.
     *
     * @param (Item\Type::*)|string $type
     * @return $this
     * @noinspection PhpDocSignatureInspection
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set a value.
     *
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set an attribute.
     */
    public function setAttribute(?string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set data.
     */
    public function setData(?Data $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set nested where item list.
     *
     * @param Item[] $itemList
     * @return self
     */
    public function setItemList(array $itemList): self
    {
        $this->value = array_map(
            function (Item $item): array {
                return $item->getRaw();
            },
            $itemList
        );

        return $this;
    }

    public function build(): Item
    {
        return Item
            ::fromRaw([
                'type' => $this->type,
                'attribute' => $this->attribute,
                'value' => $this->value,
            ])
            ->withData($this->data);
    }
}
