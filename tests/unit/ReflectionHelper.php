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

namespace tests\unit;

class ReflectionHelper
{
    private $object;

    private $reflection;


    public function __construct($object)
    {
        $this->object = $object;

        $this->reflection = new \ReflectionClass(get_class($this->object));
    }


    public function invokeMethod($methodName, array $parameters = array())
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->object, $parameters);
    }

    public function getProperty($name)
    {
        $property = $this->reflection->getProperty($name);
        $property->setAccessible(true);
        return $property->getValue($this->object);
    }

    public function setProperty($name, $value)
    {
        $property = $this->reflection->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($this->object, $value);
    }


}
