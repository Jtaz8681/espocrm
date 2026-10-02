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

namespace Espo\Core\Select\Text\FullTextSearch;

use Espo\ORM\Query\Part\Expression;

use InvalidArgumentException;

/**
 * Immutable.
 */
class Data
{
    /** @var string[] */
    private array $fieldList;
    /** @var string[] */
    private array $columnList;
    /** @var Mode::* $mode */
    private string $mode;

    /**
     * @param string[] $fieldList
     * @param string[] $columnList
     * @param Mode::* $mode
     */
    public function __construct(private Expression $expression, array $fieldList, array $columnList, string $mode)
    {
        $this->fieldList = $fieldList;
        $this->columnList = $columnList;
        $this->mode = $mode;

        if (!in_array($mode, [Mode::NATURAL_LANGUAGE, Mode::BOOLEAN])) {
            throw new InvalidArgumentException("Bad mode.");
        }
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }

    /**
     * @return string[]
     */
    public function getFieldList(): array
    {
        return $this->fieldList;
    }

    /**
     * @return string[]
     */
    public function getColumnList(): array
    {
        return $this->columnList;
    }

    /**
     * @return Mode::*
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}
