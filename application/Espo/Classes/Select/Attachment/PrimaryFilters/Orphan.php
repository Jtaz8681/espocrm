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

namespace Espo\Classes\Select\Attachment\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\Entities\Attachment;
use Espo\Entities\Settings;
use Espo\ORM\Query\SelectBuilder;

class Orphan implements Filter
{
    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->where([
            'role' => [
                Attachment::ROLE_ATTACHMENT,
                Attachment::ROLE_INLINE_ATTACHMENT,
            ],
            [
                'OR' => [
                    [
                        'parentType!=' => null,
                        'parentId' => null,
                        'relatedType' => null,
                    ],
                    [
                        'relatedType!=' => null,
                        'relatedId' => null,
                        'parentType' => null,
                    ],
                ],
            ],
            [
                'OR' => [
                    'relatedType!=' => Settings::ENTITY_TYPE,
                    'relatedType' => null,
                ],
            ],
            'attachmentChild.id' => null,
        ]);

        $queryBuilder->leftJoin(
            Attachment::ENTITY_TYPE,
            'attachmentChild',
            [
                'attachmentChild.sourceId:' => 'attachment.id',
                'attachmentChild.deleted' => false,
            ]
        );

        $queryBuilder->distinct();
    }
}
