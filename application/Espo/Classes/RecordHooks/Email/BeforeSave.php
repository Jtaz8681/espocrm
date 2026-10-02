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

namespace Espo\Classes\RecordHooks\Email;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Email;
use Espo\ORM\Entity;
use Espo\Tools\Email\Util;

/**
 * @implements SaveHook<Email>
 */
class BeforeSave implements SaveHook
{
    public function process(Entity $entity): void
    {
        if (
            $entity->getStatus() !== Email::STATUS_DRAFT &&
            $entity->getSendAt() &&
            $entity->isAttributeChanged('sendAt')
        ) {
            throw new BadRequest("Cannot set send-at if status is not Draft.");
        }

        $this->processBodyPlain($entity);
    }

    private function processBodyPlain(Email $entity): void
    {
        if (!$entity->isAttributeChanged('body')) {
            return;
        }

        $body = $entity->getBody() ?: null;

        if (!$entity->isHtml()) {
            $entity->setBodyPlain($body);

            return;
        }

        if ($body) {
            $body = Util::stripHtml($body) ?: null;
        }

        $entity->setBodyPlain($body);
    }
}
