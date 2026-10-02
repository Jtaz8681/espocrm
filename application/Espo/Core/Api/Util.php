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

namespace Espo\Core\Api;

use Espo\Core\Utils\Config;
use stdClass;

class Util
{
    public function __construct(private Config $config) {}

    public static function cloneObject(stdClass $source): stdClass
    {
        $cloned = (object) [];

        foreach (get_object_vars($source) as $k => $v) {
            $cloned->$k = self::cloneObjectItem($v);
        }

        return $cloned;
    }

    /**
     * @param mixed $item
     * @return mixed
     */
    private static function cloneObjectItem($item)
    {
        if (is_array($item)) {
            $cloned = [];

            foreach ($item as $v) {
                $cloned[] = self::cloneObjectItem($v);
            }

            return $cloned;
        }

        if ($item instanceof stdClass) {
            return self::cloneObject($item);
        }

        if (is_object($item)) {
            return clone $item;
        }

        return $item;
    }

    public function obtainIpFromRequest(Request $request): ?string
    {
        // Do not add support of any more parameters here.

        $param = $this->config->get('ipAddressServerParam') ?? 'REMOTE_ADDR';

        return $request->getServerParam($param);
    }
}
