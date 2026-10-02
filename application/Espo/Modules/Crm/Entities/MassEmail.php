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

namespace Espo\Modules\Crm\Entities;

use Espo\Core\ORM\Entity;
use Espo\Entities\EmailTemplate;
use Espo\ORM\EntityCollection;

class MassEmail extends Entity
{
    public const ENTITY_TYPE = 'MassEmail';

    public const STATUS_COMPLETE = 'Complete';
    public const STATUS_FAILED = 'Failed';
    public const STATUS_IN_PROCESS = 'In Process';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_DRAFT = 'Draft';

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function getEmailTemplateId(): ?string
    {
        return $this->get('emailTemplateId');
    }

    /**
     * @return EntityCollection<TargetList>
     */
    public function getTargetLists(): EntityCollection
    {
        /** @var EntityCollection<TargetList>  */
        return $this->relations->getMany('targetLists');
    }

    /**
     * @return EntityCollection<TargetList>
     */
    public function getExcludingTargetLists(): EntityCollection
    {
        /** @var EntityCollection<TargetList>  */
        return $this->relations->getMany('excludingTargetLists');
    }

    public function getEmailTemplate(): ?EmailTemplate
    {
        /** @var ?EmailTemplate */
        return $this->relations->getOne('emailTemplate');
    }

    public function getCampaign(): ?Campaign
    {
        /** @var ?Campaign */
        return $this->relations->getOne('campaign');
    }

    public function getCampaignId(): ?string
    {
        return $this->get('campaignId');
    }

    public function getInboundEmailId(): ?string
    {
        return $this->get('inboundEmailId');
    }

    public function getFromName(): ?string
    {
        return $this->get('fromName');
    }

    public function getReplyToName(): ?string
    {
        return $this->get('replyToName');
    }

    public function getFromAddress(): ?string
    {
        return $this->get('fromAddress');
    }

    public function getReplyToAddress(): ?string
    {
        return $this->get('replyToAddress');
    }

    public function storeSentEmails(): bool
    {
        return (bool) $this->get('storeSentEmails');
    }

    public function optOutEntirely(): bool
    {
        return (bool) $this->get('optOutEntirely');
    }

    public function setStatus(string $status): self
    {
        $this->set('status', $status);

        return $this;
    }
}
