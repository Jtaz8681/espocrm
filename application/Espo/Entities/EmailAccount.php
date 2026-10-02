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

use Espo\Core\Mail\Account\FetchData;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

use Espo\Core\Field\Date;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;

use stdClass;

class EmailAccount extends Entity
{
    public const ENTITY_TYPE = 'EmailAccount';

    public const STATUS_ACTIVE = 'Active';

    public function isAvailableForFetching(): bool
    {
        return $this->isActive() && $this->get('useImap');
    }

    public function isAvailableForSending(): bool
    {
        return $this->isActive() && $this->get('useSmtp');
    }

    public function isActive(): bool
    {
        return $this->get('status') === self::STATUS_ACTIVE;
    }

    public function getEmailAddress(): ?string
    {
        return $this->get('emailAddress');
    }

    public function getAssignedUser(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::ASSIGNED_USER);
    }

    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return LinkMultiple::create();
    }

    public function keepFetchedEmailsUnread(): bool
    {
        return (bool) $this->get('keepFetchedEmailsUnread');
    }

    public function storeSentEmails(): bool
    {
        return (bool) $this->get('storeSentEmails');
    }

    /**
     * @since 10.0.0
     */
    public function getFetchValidityNumber(): int
    {
        return $this->get(InboundEmail::FIELD_FETCH_VALIDITY_NUMBER);
    }

    public function setFetchSince(Date $fetchSince): self
    {
        return $this->setValueObject('fetchSince', $fetchSince);
    }

    public function setFetchData(FetchData $data): self
    {
        return $this->set('fetchData', $data->getRaw());
    }

    public function setFetchValidityNumber(int $number): self
    {
        return $this->set(InboundEmail::FIELD_FETCH_VALIDITY_NUMBER, $number);
    }

    public function getFetchData(): stdClass
    {
        $data = $this->get('fetchData') ?? (object) [];

        if (!property_exists($data, 'lastUID')) {
            $data->lastUID = (object) [];
        }

        if (!property_exists($data, 'lastDate')) {
            $data->lastDate = (object) [];
        }

        if (!property_exists($data, 'byDate')) {
            $data->byDate = (object) [];
        }

        return $data;
    }

    public function getFetchSince(): ?Date
    {
        /** @var ?Date */
        return $this->getValueObject('fetchSince');
    }

    public function getEmailFolder(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('emailFolder');
    }

    public function getMappedEmailFolder(string $folder): ?Link
    {
        /** @var stdClass $map */
        $map = $this->get('folderMap') ?? (object) [];

        $folderId = $map->$folder ?? null;

        if (!$folderId) {
            return null;
        }

        return Link::create($folderId);
    }

    /**
     * @return string[]
     */
    public function getMonitoredFolderList(): array
    {
        return $this->get('monitoredFolders') ?? [];
    }

    public function getHost(): ?string
    {
        return $this->get('host');
    }

    public function getPort(): ?int
    {
        return $this->get('port');
    }

    public function getUsername(): ?string
    {
        return $this->get('username');
    }

    public function getPassword(): ?string
    {
        return $this->get('password');
    }

    public function getSecurity(): ?string
    {
        return $this->get('security');
    }

    /**
     * @return ?class-string<object>
     */
    public function getImapHandlerClassName(): ?string
    {
        /** @var ?class-string<object> */
        return $this->get('imapHandler');
    }

    public function getSentFolder(): ?string
    {
        return $this->get('sentFolder');
    }

    public function getSmtpHost(): ?string
    {
        return $this->get('smtpHost');
    }

    public function getSmtpPort(): ?int
    {
        return $this->get('smtpPort');
    }

    public function getSmtpAuth(): bool
    {
        return $this->get('smtpAuth');
    }

    public function getSmtpSecurity(): ?string
    {
        return $this->get('smtpSecurity');
    }

    public function getSmtpUsername(): ?string
    {
        return $this->get('smtpUsername');
    }

    public function getSmtpPassword(): ?string
    {
        return $this->get('smtpPassword');
    }

    public function getSmtpAuthMechanism(): ?string
    {
        return $this->get('smtpAuthMechanism');
    }

    /**
     * @return ?class-string<object>
     */
    public function getSmtpHandlerClassName(): ?string
    {
        /** @var ?class-string<object> */
        return $this->get('smtpHandler');
    }
}
