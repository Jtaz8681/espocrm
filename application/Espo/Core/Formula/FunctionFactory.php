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

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Formula\Exceptions\UnknownFunction;
use Espo\Core\Formula\Functions\Base;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\ORM\Entity;
use Espo\Core\InjectableFactory;

use ReflectionClass;
use stdClass;

class FunctionFactory
{
    /** @var array<string, class-string<BaseFunction|Func|Base>> */
    private $classNameMap;

    /**
     * @param array<string, class-string<BaseFunction|Func|Base>> $classNameMap
     */
    public function __construct(
        private Processor $processor,
        private InjectableFactory $injectableFactory,
        private AttributeFetcher $attributeFetcher,
        ?array $classNameMap = null
    ) {
        $this->classNameMap = $classNameMap ?? [];
    }

    /**
     * @throws UnknownFunction
     */
    public function create(
        string $name,
        ?Entity $entity = null,
        ?stdClass $variables = null
    ): Func|FuncVariablesAware|BaseFunction|Base {

        if ($this->classNameMap && array_key_exists($name, $this->classNameMap)) {
            $className = $this->classNameMap[$name];
        } else {
            $arr = explode('\\', $name);

            foreach ($arr as $i => $part) {
                if ($i < count($arr) - 1) {
                    $part = $part . 'Group';
                }

                $arr[$i] = ucfirst($part);
            }

            $typeName = implode('\\', $arr);

            /** @var class-string<Func|FuncVariablesAware|BaseFunction|Base> $className */
            $className = 'Espo\\Core\\Formula\\Functions\\' . $typeName . 'Type';
        }

        if (!class_exists($className)) {
            throw new UnknownFunction("Unknown function: " . $name);
        }

        $class = new ReflectionClass($className);

        if (
            $class->implementsInterface(Func::class) ||
            $class->implementsInterface(FuncVariablesAware::class)
        ) {
            $binding = new BindingContainerBuilder();

            if ($entity) {
                $binding->bindInstance(Entity::class, $entity);
            }

            return $this->injectableFactory->createWithBinding($className, $binding->build());
        }

        $object = $this->injectableFactory->createWith($className, [
            'name' => $name,
            'processor' => $this->processor,
            'entity' => $entity,
            'variables' => $variables,
            'attributeFetcher' => $this->attributeFetcher,
        ]);

        if (method_exists($object, 'setAttributeFetcher')) {
            $object->setAttributeFetcher($this->attributeFetcher);
        }

        /** @var BaseFunction|Base */
        return $object;
    }
}
