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

namespace Espo\Core\Formula\Functions;

use Espo\Core\Formula\Exceptions\Error;
use Espo\ORM\Entity;
use Espo\Core\Formula\Processor;
use Espo\Core\Formula\Argument;

use stdClass;

/**
 * @deprecated Use Func interface instead.
 * @todo Remove in v11.0.
 */
abstract class Base
{
    /**
     * @var ?string
     */
    protected $name;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ?Entity
     */
    private $entity;

    /**
     * @var ?\stdClass
     */
    private $variables;

    public function __construct(
        string $name,
        Processor $processor,
        ?Entity $entity = null,
        ?stdClass $variables = null,
    ) {
        $this->name = $name;
        $this->processor = $processor;
        $this->entity = $entity;
        $this->variables = $variables;
    }

    protected function getVariables(): stdClass
    {
        return $this->variables ?? (object) [];
    }

    /**
     * @throws Error
     */
    protected function getEntity() /** @phpstan-ignore-line */
    {
        if (!$this->entity) {
            throw new Error('Formula: Entity required but not passed.');
        }

        return $this->entity;
    }

    /**
     * @return mixed
     * @throws Error
     */
    public abstract function process(stdClass $item);

    /**
     * @param mixed $item
     * @return mixed
     * @throws Error
     */
    protected function evaluate($item)
    {
        $item = new Argument($item);

        return $this->processor->process($item);
    }

    /**
     * @return mixed[]
     * @throws Error
     */
    protected function fetchArguments(stdClass $item): array
    {
        $args = $item->value ?? [];

        $eArgs = [];

        foreach ($args as $item) {
            $eArgs[] = $this->evaluate($item);
        }

        return $eArgs;
    }

    /**
     * @return mixed[]
     */
    protected function fetchRawArguments(stdClass $item): array
    {
        return $item->value ?? [];
    }
}
