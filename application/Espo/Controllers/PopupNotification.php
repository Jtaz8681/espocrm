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

namespace Espo\Controllers;

use Espo\Tools\PopupNotification\Service as Service;

use stdClass;

class PopupNotification
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function getActionGrouped(): stdClass
    {
        $grouped = $this->service->getGrouped();

        $result = (object) [];

        foreach ($grouped as $type => $itemList) {
            $rawList = array_map(
                function ($item) {
                    return (object) [
                        'id' => $item->getId(),
                        'data' => $item->getData(),
                    ];
                },
                $itemList
            );
            $result->$type = $rawList;
        }

        return $result;
    }
}
