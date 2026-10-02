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

use Espo\Core\Formula\AttributeFetcher;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use stdClass;

class AttributeType extends Base
{
    /**
     * @var AttributeFetcher
     */
    protected $attributeFetcher;

    /**
     * @return void
     */
    public function setAttributeFetcher(AttributeFetcher $attributeFetcher)
    {
        $this->attributeFetcher = $attributeFetcher;
    }

    /**
     * @return mixed
     * @throws NotAllowedUsage
     * @throws Error
     */
    public function process(stdClass $item)
    {
        if (!property_exists($item, 'value')) {
            throw new Error();
        }

        return $this->getAttributeValue($item->value);
    }

    /**
     * @param string $attribute
     * @return mixed
     * @throws NotAllowedUsage
     * @throws Error
     */
    protected function getAttributeValue($attribute)
    {
        return $this->attributeFetcher->fetch($this->getEntity(), $attribute);
    }
}
