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

namespace Espo\Core\Binding;

use LogicException;
use stdClass;

class BindingData
{
    private stdClass $global;
    private stdClass $context;

    public function __construct()
    {
        $this->global = (object) [];
        $this->context = (object) [];
    }

    public function addContext(string $className, string $key, Binding $binding): void
    {
        if (!property_exists($this->context, $className)) {
            $this->context->$className = (object) [];
        }

        $this->context->$className->$key = $binding;
    }

    public function addGlobal(string $key, Binding $binding): void
    {
        $this->global->$key = $binding;
    }

    /**
     * @param class-string<object> $className
     */
    public function hasContext(string $className, string $key): bool
    {
        if (!property_exists($this->context, $className)) {
            return false;
        }

        if (!property_exists($this->context->$className, $key)) {
            return false;
        }

        return true;
    }

    /**
     * @param class-string<object> $className
     */
    public function getContext(string $className, string $key): Binding
    {
        if (!$this->hasContext($className, $key)) {
            throw new LogicException("No data.");
        }

        return $this->context->$className->$key;
    }

    public function hasGlobal(string $key): bool
    {
        if (!property_exists($this->global, $key)) {
            return false;
        }

        return true;
    }

    public function getGlobal(string $key): Binding
    {
        if (!$this->hasGlobal($key)) {
            throw new LogicException("No data.");
        }

        return $this->global->$key;
    }

    /**
     * @return string[]
     */
    public function getGlobalKeyList(): array
    {
        return array_keys(
            get_object_vars($this->global)
        );
    }

    /**
     * @return class-string<object>[]
     */
    public function getContextList(): array
    {
        /** @var class-string<object>[] */
        return array_keys(
            get_object_vars($this->context)
        );
    }

    /**
     * @return string[]
     */
    public function getContextKeyList(string $context): array
    {
        return array_keys(
            get_object_vars($this->context->$context ?? (object) [])
        );
    }
}
