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

use Espo\Core\Acl;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\EmailTemplate;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

class Service
{
    public function __construct(
        private Processor $processor,
        private User $user,
        private Acl $acl,
        private EntityManager $entityManager
    ) {}

    /**
     * Prepare an email data with an applied template.
     *
     * @throws NotFound
     * @throws ForbiddenSilent
     */
    public function process(string $emailTemplateId, Data $data, ?Params $params = null): Result
    {
        /** @var ?EmailTemplate $emailTemplate */
        $emailTemplate = $this->entityManager->getEntityById(EmailTemplate::ENTITY_TYPE, $emailTemplateId);

        if (!$emailTemplate) {
            throw new NotFound();
        }

        $params ??= Params::create()
            ->withApplyAcl(true)
            ->withCopyAttachments(true);

        if (
            $params->applyAcl() &&
            !$this->acl->checkEntityRead($emailTemplate)
        ) {
            throw new ForbiddenSilent();
        }

        if (!$data->getUser()) {
            $data = $data->withUser($this->user);
        }

        return $this->processor->process($emailTemplate, $params, $data);
    }
}
