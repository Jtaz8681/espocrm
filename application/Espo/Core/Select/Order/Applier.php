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

namespace Espo\Core\Select\Order;

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\ORM\Type\FieldType;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\OrderList;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Select\Order\Item as OrderItem;
use Espo\Core\Select\Order\Params as OrderParams;
use Espo\Core\Select\SearchParams;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

use RuntimeException;

class Applier
{
    public function __construct(
        private string $entityType,
        private MetadataProvider $metadataProvider,
        private ItemConverterFactory $itemConverterFactory,
        private OrdererFactory $ordererFactory,
        private AclManager $aclManager,
        private User $user,
        private Acl\SystemRestriction $systemRestriction,
    ) {}

    /**
     * @throws Forbidden
     * @throws BadRequest
     */
    public function apply(QueryBuilder $queryBuilder, OrderParams $params): void
    {
        if ($params->forceDefault()) {
            $this->applyDefaultOrder($queryBuilder, $params->getOrder());

            return;
        }

        $orderBy = $params->getOrderBy();

        if ($orderBy) {
            if (
                str_contains($orderBy, '.') ||
                str_contains($orderBy, ':')
            ) {
                throw new Forbidden("Complex expressions are forbidden in 'orderBy'.");
            }

            if ($this->metadataProvider->isFieldOrderDisabled($this->entityType, $orderBy)) {
                throw new Forbidden("Order by the field '$orderBy' is disabled.");
            }

            if ($this->metadataProvider->getFieldType($this->entityType, $orderBy) === FieldType::PASSWORD) {
                throw new Forbidden("Order by field '$orderBy' is not allowed.");
            }

            if (
                $params->applyPermissionCheck() &&
                !$this->aclManager->checkField($this->user, $this->entityType, $orderBy)
            ) {
                throw new Forbidden("Not access to order by field '$orderBy'.");
            }

            if (
                !$params->applyPermissionCheck() &&
                !$this->systemRestriction->checkFieldRead($this->entityType, $orderBy)
            ) {
                throw new Forbidden("Cannot order by restricted field '$orderBy'.");
            }
        }

        if ($orderBy === null) {
            $orderBy = $this->metadataProvider->getDefaultOrderBy($this->entityType);
        }

        if (!$orderBy) {
            return;
        }

        $this->applyOrder($queryBuilder, $orderBy, $params->getOrder());
    }

    /**
     * @param SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null $order
     * @throws BadRequest
     */
    private function applyDefaultOrder(QueryBuilder $queryBuilder, ?string $order): void
    {
        $orderBy = $this->metadataProvider->getDefaultOrderBy($this->entityType);

        if (!$orderBy) {
            $queryBuilder->order(Attribute::ID, $order);

            return;
        }

        if (!$order) {
            $order = $this->metadataProvider->getDefaultOrder($this->entityType);

            if ($order && strtolower($order) === 'desc') {
                $order = SearchParams::ORDER_DESC;
            } else if ($order && strtolower($order) === 'asc') {
                $order = SearchParams::ORDER_ASC;
            } else if ($order !== null) {
                throw new RuntimeException("Bad default order.");
            }
        }

        /** @var SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null $order */

        $this->applyOrder($queryBuilder, $orderBy, $order);
    }

    /**
     * @param SearchParams::ORDER_ASC|SearchParams::ORDER_DESC|null $order
     * @throws BadRequest
     */
    private function applyOrder(QueryBuilder $queryBuilder, string $orderBy, ?string $order): void
    {
        if (!$orderBy) {
            throw new RuntimeException("Could not apply empty order.");
        }

        if ($order === null) {
            $order = SearchParams::ORDER_ASC;
        }

        $hasOrderer = $this->ordererFactory->has($this->entityType, $orderBy);

        if ($hasOrderer) {
            $orderer = $this->ordererFactory->create($this->entityType, $orderBy);

            $orderer->apply(
                $queryBuilder,
                OrderItem::create($orderBy, $order)
            );

            if ($order !== Attribute::ID) {
                $queryBuilder->order(Attribute::ID, $order);
            }

            return;
        }

        $resultOrderBy = $orderBy;

        $type = $this->metadataProvider->getFieldType($this->entityType, $orderBy);

        $hasItemConverter = $this->itemConverterFactory->has($this->entityType, $orderBy);

        if ($hasItemConverter) {
            $converter = $this->itemConverterFactory->create($this->entityType, $orderBy);

            $resultOrderBy = $this->orderListToArray(
                $converter->convert(
                    OrderItem::create($orderBy, $order)
                )
            );
        } else if (in_array($type, [FieldType::LINK, FieldType::FILE, FieldType::IMAGE, FieldType::LINK_ONE])) {
            $resultOrderBy .= 'Name';
        } else if ($type === FieldType::LINK_PARENT) {
            $resultOrderBy .= 'Type';
        } else if (
            !$this->metadataProvider->hasAttribute($this->entityType, $orderBy)
        ) {
            throw new BadRequest("Order by non-existing field '$orderBy'.");
        }

        $orderByAttribute = null;

        if (!is_array($resultOrderBy)) {
            $orderByAttribute = $resultOrderBy;

            $resultOrderBy = [
                [$resultOrderBy, $order]
            ];
        }

        if (
            $orderBy !== Attribute::ID &&
            (
                !$orderByAttribute ||
                !$this->metadataProvider->isAttributeParamUniqueTrue($this->entityType, $orderByAttribute)
            ) &&
            $this->metadataProvider->hasAttribute($this->entityType, Attribute::ID)
        ) {
            $resultOrderBy[] = [Attribute::ID, $order];
        }

        $queryBuilder->order($resultOrderBy);
    }

    /**
     * @return array<array{string, string}>
     */
    private function orderListToArray(OrderList $orderList): array
    {
        $list = [];

        foreach ($orderList as $order) {
            $list[] = [
                $order->getExpression()->getValue(),
                $order->getDirection(),
            ];
        }

        return $list;
    }
}
