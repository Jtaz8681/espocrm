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

use Espo\Core\Templates\Entities\Company;
use Espo\Core\Templates\Entities\Person;
use Espo\Tools\LinkManager\Hook\DeleteHook;
use Espo\Tools\LinkManager\Params;
use Espo\Tools\LinkManager\Type;
use Espo\Modules\Crm\Entities\TargetList;

use Espo\Core\Utils\Metadata;

class TargetListDelete implements DeleteHook
{
    public function __construct(private Metadata $metadata)
    {}

    public function process(Params $params): void
    {
        $toProcess =
            (
                $params->getEntityType() === TargetList::ENTITY_TYPE ||
                $params->getForeignEntityType() === TargetList::ENTITY_TYPE
            ) &&
            $params->getType() === Type::MANY_TO_MANY;

        if (!$toProcess) {
            return;
        }

        [$entityType, $link, $foreignLink] = $params->getEntityType() === TargetList::ENTITY_TYPE ?
            [
                $params->getForeignEntityType(),
                $params->getForeignLink(),
                $params->getLink(),
            ] :
            [
                $params->getEntityType(),
                $params->getLink(),
                $params->getForeignLink(),
            ];

        if (!$entityType) {
            return;
        }

        $type = $this->metadata->get(['scopes', $entityType, 'type']);

        if (!in_array($type, [Person::TEMPLATE_TYPE, Company::TEMPLATE_TYPE])) {
            return;
        }

        if ($link !== 'targetLists') {
            return;
        }

        $this->processInternal($entityType, $link, $foreignLink);
    }

    private function processInternal(string $entityType, string $link, string $foreignLink): void
    {
        $this->metadata->delete('entityDefs', $entityType, ['fields.targetListIsOptedOut']);

        $targetLinkList = $this->metadata->get(['scopes', TargetList::ENTITY_TYPE, 'targetLinkList']) ?? [];

        if (in_array($foreignLink, $targetLinkList)) {
            $targetLinkList = array_diff($targetLinkList, [$foreignLink]);

            $this->metadata->set('scopes', TargetList::ENTITY_TYPE, [
                'targetLinkList' => $targetLinkList,
            ]);
        }

        $this->metadata->delete('clientDefs', TargetList::ENTITY_TYPE, ["relationshipPanels.$foreignLink"]);
        $this->metadata->delete('recordDefs', TargetList::ENTITY_TYPE, ["relationships.$foreignLink"]);

        $this->metadata->save();
    }
}
