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

// Aliases for backward compatibility or patches.
$map = [
    'Espo\\Core\\ORM\\Repository\\Option\\SaveContext' => 'Espo\\ORM\\Repository\\Option\\SaveContext',
    'Doctrine\\DBAL\\Platforms\\Keywords\\MariaDb102Keywords' => 'Espo\\Core\\Utils\\Database\\Dbal\\Platforms\\Keywords\\MariaDb102Keywords',
];

/** @phpstan-ignore-next-line  */
foreach ($map as $alias => $className) {
    if (!class_exists($className)) {
        continue;
    }

    class_alias($className, $alias);
}
