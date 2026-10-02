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

use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Node;
use Espo\Core\Formula\Parser\Ast\Value;
use Espo\Core\Formula\Parser\Ast\Variable;

/**
 * A function argument.
 */
class Argument implements Evaluatable
{
    public function __construct(private mixed $data)
    {}

    /**
     * Get an argument type (function name).
     *
     * @throws Error
     */
    public function getType(): string
    {
        if ($this->data instanceof Node) {
            return $this->data->getType();
        }

        if ($this->data instanceof Value) {
            return 'value';
        }

        if ($this->data instanceof Variable) {
            return 'variable';
        }

        if ($this->data instanceof Attribute) {
            return 'attribute';
        }

        throw new Error("Can't get type from scalar.");
    }

    /**
     * Get a nested argument list.
     *
     * @throws Error
     */
    public function getArgumentList(): ArgumentList
    {
        if ($this->data instanceof Node) {
            return new ArgumentList($this->data->getChildNodes());
        }

        if ($this->data instanceof Value) {
            return new ArgumentList([$this->data->getValue()]);
        }

        if ($this->data instanceof Variable) {
            $value = new Value($this->data->getName());

            return new ArgumentList([$value]);
        }

        if ($this->data instanceof Attribute) {
            return new ArgumentList([$this->data->getName()]);
        }

        throw new Error("Can't get argument list from a non-node item.");
    }

    /**
     * Get data.
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}
