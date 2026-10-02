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

namespace Espo\Entities;

use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use stdClass;

class LeadCapture extends Entity
{
    public const ENTITY_TYPE = 'LeadCapture';

    /**
     * @deprecated As of v7.2.
     */
    public function isToSubscribeContactIfExists(): bool
    {
        return $this->get('subscribeToTargetList') && $this->get('subscribeContactToTargetList');
    }

    public function isActive(): bool
    {
        return (bool) $this->get('isActive');
    }

    public function hasFormCaptcha(): bool
    {
        return (bool) $this->get('formCaptcha');
    }

    /**
     * @return string[]
     */
    public function getFormFrameAncestors(): array
    {
        return $this->get('formFrameAncestors') ?? [];
    }

    public function getFormText(): ?string
    {
        return $this->get('formText');
    }

    public function getFormSuccessText(): ?string
    {
        return $this->get('formSuccessText');
    }

    public function getFormLanguage(): ?string
    {
        return $this->get('formLanguage');
    }

    public function getFormSuccessRedirectUrl(): ?string
    {
        $url = $this->get('formSuccessRedirectUrl');

        if (!$url) {
            return null;
        }

        if (!str_contains($url, '://')) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    /**
     * @return string[]
     */
    public function getFieldList(): array
    {
        return $this->get('fieldList') ?? [];
    }

    public function isFieldRequired(string $field): bool
    {
        /** @var stdClass $fieldParams */
        $fieldParams = $this->get('fieldParams') ?? (object) [];
        /** @var stdClass $itParams */
        $itParams = $fieldParams->$field ?? (object) [];

        return (bool) ($itParams->required ?? false);
    }

    public function getOptInConfirmationSuccessMessage(): ?string
    {
        return $this->get('optInConfirmationSuccessMessage');
    }

    public function duplicateCheck(): bool
    {
        return (bool) $this->get('duplicateCheck');
    }

    public function skipOptInConfirmationIfSubscribed(): bool
    {
        return (bool) $this->get('skipOptInConfirmationIfSubscribed');
    }

    public function createLeadBeforeOptInConfirmation(): bool
    {
        return (bool) $this->get('createLeadBeforeOptInConfirmation');
    }

    public function optInConfirmation(): bool
    {
        return (bool) $this->get('optInConfirmation');
    }

    public function getOptInConfirmationLifetime(): ?int
    {
        return $this->get('optInConfirmationLifetime');
    }

    public function subscribeToTargetList(): bool
    {
        return (bool) $this->get('subscribeToTargetList');
    }

    public function subscribeContactToTargetList(): bool
    {
        return (bool) $this->get('subscribeContactToTargetList');
    }

    public function getFormId(): ?string
    {
        return $this->get('formId');
    }

    public function setFormId(string $apiKey): self
    {
        return $this->set('formId', $apiKey);
    }

    public function getApiKey(): ?string
    {
        return $this->get('apiKey');
    }

    public function setApiKey(string $apiKey): self
    {
        $this->set('apiKey', $apiKey);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function getTargetTeamId(): ?string
    {
        return $this->get('targetTeamId');
    }

    public function getTargetListId(): ?string
    {
        return $this->get('targetListId');
    }

    public function getCampaignId(): ?string
    {
        return $this->get('campaignId');
    }

    public function getInboundEmailId(): ?string
    {
        return $this->get('inboundEmailId');
    }

    public function getLeadSource(): ?string
    {
        return $this->get('leadSource');
    }

    public function getOptInConfirmationEmailTemplateId(): ?string
    {
        return $this->get('optInConfirmationEmailTemplateId');
    }

    /**
     * @since 8.1.0
     */
    public function getPhoneNumberCountry(): ?string
    {
        return $this->get('phoneNumberCountry');
    }

    public function hasFormEnabled(): bool
    {
        return (bool) $this->get('formEnabled');
    }

    /**
     * @since 9.1.0
     */
    public function getFormTitle(): ?string
    {
        return $this->get('formTitle');
    }

    /**
     * @since 9.1.0
     */
    public function getFormTheme(): ?string
    {
        return $this->get('formTheme');
    }

    public function getPipeline(): ?Pipeline
    {
        /** @var ?Pipeline */
        return $this->relations->getOne(Field::PIPELINE);
    }
}
