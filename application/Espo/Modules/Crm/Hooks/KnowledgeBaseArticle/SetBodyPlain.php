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

namespace Espo\Modules\Crm\Hooks\KnowledgeBaseArticle;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\Email\Util as EmailUtil;

/**
 * @implements BeforeSave<KnowledgeBaseArticle>
 */
class SetBodyPlain implements BeforeSave
{
    private const ATTR_BODY = 'body';
    private const ATTR_BODY_PLAIN = 'bodyPlain';

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged(self::ATTR_BODY)) {
            return;
        }

        $bodyPlain = $this->stripHtml($entity->getBody());

        $entity->set(self::ATTR_BODY_PLAIN, $bodyPlain);
    }

    private function stripHtml(?string $body): ?string
    {
        if (!$body) {
            return null;
        }

        return EmailUtil::stripHtml($body) ?: null;
    }
}
