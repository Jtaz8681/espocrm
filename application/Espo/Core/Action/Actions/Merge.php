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

namespace Espo\Core\Action\Actions;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Action\Action;
use Espo\Core\Action\Actions\Merge\Merger;
use Espo\Core\Action\Data;
use Espo\Core\Action\Params;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;

use stdClass;

class Merge implements Action
{
    public function __construct(private Acl $acl, private Merger $merger)
    {}

    public function process(Params $params, Data $data): void
    {
        $entityType = $params->getEntityType();

        if (!$this->acl->checkScope($entityType, Table::ACTION_EDIT)) {
            throw new Forbidden();
        }

        $sourceIdList = $data->get('sourceIdList');
        $attributes = $data->get('attributes');

        if (!is_array($sourceIdList)) {
            throw new BadRequest("No 'sourceIdList'.");
        }

        if (!$attributes instanceof stdClass) {
            throw new BadRequest("No 'attributes'.");
        }

        $this->merger->process($params, $sourceIdList, $attributes);
    }
}
