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

use Espo\Core\Container;
use Espo\Core\Utils\Metadata;
use Espo\Core\Templates\Entities\Event;

class AfterUpgrade
{
    public function run(Container $container): void
    {
        $this->updateMetadata($container->get('metadata'));
    }

    private function updateMetadata(Metadata $metadata): void
    {
        $this->updateEventMetadata($metadata);

        $metadata->save();
    }

    private function updateEventMetadata(Metadata $metadata): void
    {
        $defs = $metadata->get(['scopes']);

        foreach ($defs as $entityType => $item) {
            $isCustom = $item['isCustom'] ?? false;
            $type = $item['type'] ?? false;

            if (!$isCustom || $type !== Event::TEMPLATE_TYPE) {
                continue;
            }

            if (!is_string($metadata->get(['entityDefs', $entityType, 'fields', 'duration', 'select']))) {
                continue;
            }

            $metadata->delete('entityDefs', $entityType, 'fields.duration.orderBy');

            $metadata->set('entityDefs', $entityType, [
                'fields' => [
                    'duration' => [
                        'select' => [
                            'select' => "TIMESTAMPDIFF_SECOND:(dateStart, dateEnd)"
                        ],
                        'order' => [
                            'order' => [["TIMESTAMPDIFF_SECOND:(dateStart, dateEnd)", "{direction}"]]
                        ],
                    ]
                ]
            ]);
        }
    }
}
