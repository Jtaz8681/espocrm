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

namespace Espo\Core\Record\Hook;

use Espo\Core\Acl;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;

use Espo\Entities\User;
use ReflectionClass;
use RuntimeException;

class Provider
{
    /** @var array<string, object[]> */
    private $map = [];

    /** @var array<string, class-string[]> */
    private $typeInterfaceListMap = [
        Type::BEFORE_READ => [ReadHook::class],
        Type::EARLY_BEFORE_CREATE => [CreateHook::class, SaveHook::class],
        Type::BEFORE_CREATE => [CreateHook::class, SaveHook::class],
        Type::AFTER_CREATE => [CreateHook::class, SaveHook::class],
        Type::EARLY_BEFORE_UPDATE => [UpdateHook::class, SaveHook::class],
        Type::BEFORE_UPDATE => [UpdateHook::class, SaveHook::class],
        Type::AFTER_UPDATE => [UpdateHook::class, SaveHook::class],
        Type::BEFORE_DELETE => [DeleteHook::class],
        Type::AFTER_DELETE => [DeleteHook::class],
        Type::BEFORE_LINK => [LinkHook::class],
        Type::BEFORE_UNLINK => [UnlinkHook::class],
        Type::AFTER_LINK => [LinkHook::class],
        Type::AFTER_UNLINK => [UnlinkHook::class],
    ];

    private BindingContainer $bindingContainer;

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private Acl $acl,
        private User $user
    ) {
        $this->bindingContainer = BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->bindInstance(Acl::class, $this->acl)
            ->build();
    }

    /**
     * @return object[]
     */
    public function getList(string $entityType, string $type): array
    {
        $key = $entityType . '_' . $type;

        if (!array_key_exists($key, $this->map)) {
            $this->map[$key] = $this->loadList($entityType, $type);
        }

        return $this->map[$key];
    }

    /**
     * @return object[]
     */
    private function loadList(string $entityType, string $type): array
    {
        $key = $type . 'HookClassNameList';

        /** @var class-string[] $classNameList */
        $classNameList = [
            ...$this->metadata->get("app.record.$key", []),
            ...$this->metadata->get("recordDefs.$entityType.$key", [])
        ];

        $interfaces = $this->typeInterfaceListMap[$type] ?? null;

        if (!$interfaces) {
            throw new RuntimeException("Unsupported record hook type '$type'.");
        }

        $list = [];

        foreach ($classNameList as $className) {
            $class = new ReflectionClass($className);

            $found = false;

            foreach ($interfaces as $interface) {
                if ($class->implementsInterface($interface)) {
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                throw new RuntimeException("Hook '$className' does not implement any required interface.");
            }

            $list[] = $this->injectableFactory->createWithBinding($className, $this->bindingContainer);
        }

        return $list;
    }
}
