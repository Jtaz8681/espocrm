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

namespace Espo\Core\FieldValidation;

class FieldValidationParams
{
    /** @var string[] */
    private $skipFieldList = [];
    /** @var array<string, string[]> */
    private $typeSkipFieldListData = [];

    public function __construct() {}

    /**
     * A field list that will be skipped when validating.
     *
     * @return string[] A field list.
     */
    public function getSkipFieldList(): array
    {
        return $this->skipFieldList;
    }

    /**
     * A field list that will be skipped in validation for a specific validation type.
     *
     * @param string $type A validation type.
     * @return string[] A field list.
     */
    public function getTypeSkipFieldList(string $type): array
    {
        return $this->typeSkipFieldListData[$type] ?? [];
    }

    /**
     * Clone with a specified field list that will be skipped when validating.
     *
     * @param string[] $list A field list.
     */
    public function withSkipFieldList(array $list): self
    {
        $obj = clone $this;
        $obj->skipFieldList = $list;

        return $obj;
    }

    /**
     * Clone with a specified field list that will be skipped in validation for a specific validation type.
     *
     * @param string $type A validation type.
     * @param string[] $list A field list.
     */
    public function withTypeSkipFieldList(string $type, array $list): self
    {
        $obj = clone $this;
        $obj->typeSkipFieldListData[$type] = $list;

        return $obj;
    }

    /**
     * Create an empty instance.
     */
    public static function create(): self
    {
        return new self();
    }
}
