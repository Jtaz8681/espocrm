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

namespace Espo\Core\Select\Order\ItemConverters;

use Espo\ORM\Query\Part\OrderList;
use Espo\ORM\Query\Part\Order;

use Espo\Core\Select\Order\Item;
use Espo\Core\Select\Order\ItemConverter;

class AddressType implements ItemConverter
{
    public function convert(Item $item): OrderList
    {
        $orderBy = $item->getOrderBy();
        $order = $item->getOrder();

        return OrderList::create([
            Order::fromString($orderBy . 'Country')->withDirection($order),
            Order::fromString($orderBy . 'City')->withDirection($order),
            Order::fromString($orderBy . 'Street')->withDirection($order),
        ]);
    }
}
