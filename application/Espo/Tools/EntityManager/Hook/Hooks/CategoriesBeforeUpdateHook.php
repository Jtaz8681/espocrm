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

namespace Espo\Tools\EntityManager\Hook\Hooks;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error\Body;
use Espo\Tools\EntityManager\Hook\UpdateHook;
use Espo\Tools\EntityManager\Params;

/**
 * @noinspection PhpUnused
 */
class CategoriesBeforeUpdateHook implements UpdateHook
{
    private const string PARAM = 'categories';

    public function process(Params $params, Params $previousParams): void
    {
        if ($params->get(self::PARAM) && $params->get('kanbanViewMode')) {
            throw Conflict::createWithBody(
                'cannotEnableCategoriesWithKanbanViewMode',
                Body::create()->withMessageTranslation('cannotEnableCategoriesWithKanbanViewMode', 'EntityManager')
            );
        }
    }
}
