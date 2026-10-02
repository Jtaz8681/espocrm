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

class BeforeUpgrade
{
    public function run(): void
    {
        if (php_sapi_name() === 'cli' && !in_array('-s', $_SERVER['argv'])) {
            echo "\n\nPlease re-run upgrade command with -s parameter:\n";
            echo "  php command.php upgrade -s\n\n";

            throw new \Espo\Core\Exceptions\Error("Need -s parameter.");
        }
    }
}
