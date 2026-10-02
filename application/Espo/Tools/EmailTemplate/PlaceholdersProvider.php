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

namespace Espo\Tools\EmailTemplate;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

class PlaceholdersProvider
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * @return array{string, Placeholder}[]
     */
    public function get(): array
    {
        $defs = $this->metadata->get("app.emailTemplate.placeholders") ?? [];

        /** @var string[] $list */
        $list = array_keys($defs);

        usort($list, function ($a, $b) use ($defs) {
            $o1 = $defs[$a]['order'] ?? 0;
            $o2 = $defs[$b]['order'] ?? 0;

            return $o1 - $o2;
        });

        return array_map(function ($name) use ($defs) {
            /** @var class-string<Placeholder> $className */
            $className = $defs[$name]['className'];

            $placeholder = $this->injectableFactory->create($className);

            return [$name, $placeholder];
        }, $list);
    }
}
