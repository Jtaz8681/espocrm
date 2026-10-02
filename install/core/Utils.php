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

class Utils
{
    static public $actionPath = 'install/core/actions';

    static public function checkActionExists(string $actionName): bool
    {
        return in_array($actionName, [
            'saveSettings',
            'buildDatabase',
            'checkPermission',
            'createUser',
            'errors',
            'finish',
            'main',
            'saveEmailSettings',
            'savePreferences',
            'settingsTest',
            'setupConfirmation',
            'step1',
            'step2',
            'step3',
            'step4',
            'step5',
        ]);
    }
}
