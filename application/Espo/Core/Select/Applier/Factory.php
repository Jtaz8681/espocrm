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

namespace Espo\Core\Select\Applier;

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Select\Text\Applier as TextFilterApplier;
use Espo\Core\Select\AccessControl\Applier as AccessControlFilterApplier;
use Espo\Core\Select\Where\Applier as WhereApplier;
use Espo\Core\Select\Select\Applier as SelectApplier;
use Espo\Core\Select\Primary\Applier as PrimaryFilterApplier;
use Espo\Core\Select\Order\Applier as OrderApplier;
use Espo\Core\Select\Bool\Applier as BoolFilterListApplier;
use Espo\Core\Select\Applier\Appliers\Additional as AdditionalApplier;
use Espo\Core\Select\Applier\Appliers\Limit as LimitApplier;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;

use Espo\Core\Utils\Acl\UserAclManagerProvider;
use Espo\Entities\User;
use RuntimeException;

class Factory
{
    public const SELECT = 'select';
    public const WHERE = 'where';
    public const ORDER = 'order';
    public const LIMIT = 'limit';
    public const ACCESS_CONTROL_FILTER = 'accessControlFilter';
    public const TEXT_FILTER = 'textFilter';
    public const PRIMARY_FILTER = 'primaryFilter';
    public const BOOL_FILTER_LIST = 'boolFilterList';
    public const ADDITIONAL = 'additional';

    /**
     * @var array<string, class-string<object>>
     */
    private array $defaultClassNameMap = [
        self::TEXT_FILTER => TextFilterApplier::class,
        self::ACCESS_CONTROL_FILTER => AccessControlFilterApplier::class,
        self::WHERE => WhereApplier::class,
        self::SELECT => SelectApplier::class,
        self::PRIMARY_FILTER => PrimaryFilterApplier::class,
        self::ORDER => OrderApplier::class,
        self::BOOL_FILTER_LIST => BoolFilterListApplier::class,
        self::ADDITIONAL => AdditionalApplier::class,
        self::LIMIT => LimitApplier::class,
    ];

    public function __construct(
        private InjectableFactory $injectableFactory,
        private UserAclManagerProvider $userAclManagerProvider,
    ) {}

    private function create(string $entityType, User $user, string $type): object
    {
        $className = $this->getDefaultClassName($type);

        $aclManager = $this->userAclManagerProvider->get($user);
        $acl = $aclManager->createUserAcl($user);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $user)
            ->bindInstance(AclManager::class, $aclManager)
            ->bindInstance(Acl::class, $acl);

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    public function createWhere(string $entityType, User $user): WhereApplier
    {
        /** @var WhereApplier */
        return $this->create($entityType, $user, self::WHERE);
    }

    public function createSelect(string $entityType, User $user): SelectApplier
    {
        /** @var SelectApplier */
        return $this->create($entityType, $user, self::SELECT);
    }

    public function createOrder(string $entityType, User $user): OrderApplier
    {
        /** @var OrderApplier */
        return $this->create($entityType, $user, self::ORDER);
    }

    public function createLimit(string $entityType, User $user): LimitApplier
    {
        /** @var LimitApplier */
        return $this->create($entityType, $user, self::LIMIT);
    }

    public function createAccessControlFilter(string $entityType, User $user): AccessControlFilterApplier
    {
        /** @var AccessControlFilterApplier */
        return $this->create($entityType, $user, self::ACCESS_CONTROL_FILTER);
    }

    public function createTextFilter(string $entityType, User $user): TextFilterApplier
    {
        /** @var TextFilterApplier */
        return $this->create($entityType, $user, self::TEXT_FILTER);
    }

    public function createPrimaryFilter(string $entityType, User $user): PrimaryFilterApplier
    {
        /** @var PrimaryFilterApplier */
        return $this->create($entityType, $user, self::PRIMARY_FILTER);
    }

    public function createBoolFilterList(string $entityType, User $user): BoolFilterListApplier
    {
        /** @var BoolFilterListApplier */
        return $this->create($entityType, $user, self::BOOL_FILTER_LIST);
    }

    public function createAdditional(string $entityType, User $user): AdditionalApplier
    {
        /** @var AdditionalApplier */
        return $this->create($entityType, $user, self::ADDITIONAL);
    }

    /**
     * @return class-string<object>
     */
    private function getDefaultClassName(string $type): string
    {
        if (array_key_exists($type, $this->defaultClassNameMap)) {
            return $this->defaultClassNameMap[$type];
        }

        throw new RuntimeException("Applier `$type` does not exist.");
    }
}
