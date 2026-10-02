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

namespace Espo\Classes\Record\Attachment;

use Espo\Core\Record\Input\Data;
use Espo\Core\Record\Input\Filter;

/**
 * @noinspection PhpUnused
 */
class UpdateInputFilter implements Filter
{
    public function filter(Data $data): void
    {
        $data->clear('parentId');
        $data->clear('parentType');
        $data->clear('relatedId');
        $data->clear('relatedType');
        $data->clear('isBeingUploaded');
        $data->clear('storage');
    }
}
