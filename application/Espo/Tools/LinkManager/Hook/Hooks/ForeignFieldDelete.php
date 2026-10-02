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

namespace Espo\Tools\LinkManager\Hook\Hooks;

use Espo\Core\ORM\Type\FieldType;
use Espo\Tools\LinkManager\Hook\DeleteHook;
use Espo\Tools\LinkManager\Params;
use Espo\Core\Utils\Metadata;

use Espo\ORM\Defs;

class ForeignFieldDelete implements DeleteHook
{
    public function __construct(
        private Metadata $metadata,
        private Defs $defs
    ) {}

    public function process(Params $params): void
    {
        $this->processInternal($params->getEntityType(), $params->getLink());

        if ($params->getForeignEntityType()) {
            $this->processInternal($params->getForeignEntityType(), $params->getForeignLink());
        }
    }

    private function processInternal(string $entityType, string $link): void
    {
        if (!$this->defs->hasEntity($entityType)) {
            return;
        }

        foreach ($this->defs->getEntity($entityType)->getFieldList() as $fieldDefs) {
            if ($fieldDefs->getType() !== FieldType::FOREIGN) {
                continue;
            }

            if ($fieldDefs->getParam('link') === $link) {
                $this->deleteForeignField($entityType, $fieldDefs->getName());
            }
        }
    }

    private function deleteForeignField(string $entityType, string $field): void
    {
        $this->metadata->delete('entityDefs', $entityType, ['fields.' . $field]);

        $this->metadata->save();
    }
}
