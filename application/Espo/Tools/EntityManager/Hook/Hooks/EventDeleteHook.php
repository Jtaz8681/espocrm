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

use Espo\Core\Templates\Entities\Event;
use Espo\Core\Utils\Language;
use Espo\Tools\EntityManager\Hook\DeleteHook;
use Espo\Tools\EntityManager\Params;

class EventDeleteHook implements DeleteHook
{
    public function __construct(
        private Language $baseLanguage,
        private Language $language
    ) {}

    public function process(Params $params): void
    {
        if (
            $params->getType() !== Event::TEMPLATE_TYPE
        ) {
            return;
        }

        $name = $params->getName();

        $label1 = 'Schedule ' . $name;
        $label2 = 'Log ' . $name;

        $this->baseLanguage->delete('Global', 'labels', $label1);
        $this->baseLanguage->delete('Global', 'labels', $label2);

        $this->language->delete('Global', 'labels', $label1);
        $this->language->delete('Global', 'labels', $label2);

        $this->baseLanguage->save();
        $this->language->save();
    }
}
