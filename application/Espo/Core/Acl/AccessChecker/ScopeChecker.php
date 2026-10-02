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

namespace Espo\Core\Acl\AccessChecker;

use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Table;

/**
 * Checks scope access.
 */
class ScopeChecker
{
    public function __construct()
    {}

    public function check(ScopeData $data, ?string $action = null, ?ScopeCheckerData $checkerData = null): bool
    {
        if ($data->isFalse()) {
            return false;
        }

        if ($data->isTrue()) {
            return true;
        }

        if ($action === null) {
            return true;
        }

        $level = $data->get($action);

        if ($level === Table::LEVEL_ALL || $level === Table::LEVEL_YES) {
            return true;
        }

        if ($level === Table::LEVEL_NO) {
            return false;
        }

        if (!$checkerData) {
            return false;
        }

        if ($level === Table::LEVEL_OWN || $level === Table::LEVEL_TEAM) {
            if ($checkerData->isOwn()) {
                return true;
            }
        }

        if ($level === Table::LEVEL_OWN || $level === Table::LEVEL_TEAM) {
            if ($checkerData->isShared()) {
                return true;
            }
        }

        if ($level === Table::LEVEL_TEAM) {
            if ($checkerData->inTeam()) {
                return true;
            }
        }

        return false;
    }
}
