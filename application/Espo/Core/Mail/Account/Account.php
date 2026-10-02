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

namespace Espo\Core\Mail\Account;

use Espo\Core\Field\Date;
use Espo\Core\Field\DateTime;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Mail\SmtpParams;
use Espo\Entities\Email;

interface Account
{
    /**
     * Update fetch-data.
     */
    public function updateFetchData(FetchData $fetchData): void;

    /**
     * Update connected-at.
     */
    public function updateConnectedAt(): void;

    /**
     * Relate an email with the account.
     */
    public function relateEmail(Email $email): void;

    /**
     * Max number of emails to be fetched per iteration.
     */
    public function getPortionLimit(): int;

    /**
     * Is available for fetching.
     */
    public function isAvailableForFetching(): bool;

    /**
     * An email address of the account.
     */
    public function getEmailAddress(): ?string;

    /**
     * A user fetched emails will be assigned to (through the `assignedUsers` field).
     */
    public function getAssignedUser(): ?Link;

    /**
     * A user the account belongs to.
     */
    public function getUser(): ?Link;

    /**
     * Users email should be related to (put into inbox).
     */
    public function getUsers(): LinkMultiple;

    /**
     * Teams email should be related to.
     */
    public function getTeams(): LinkMultiple;

    /**
     * Fetched emails won't be marked as read upon fetching.
     */
    public function keepFetchedEmailsUnread(): bool;

    /**
     * Get fetch-data.
     */
    public function getFetchData(): FetchData;

    /**
     * Fetch email since a specific date.
     */
    public function getFetchSince(): ?Date;

    /**
     * A folder fetched emails should be put into.
     */
    public function getEmailFolder(): ?Link;

    /**
     * A group folder fetched emails should be put into.
     */
    public function getGroupEmailFolder(): ?Link;

    /**
     * Folders to fetch from.
     *
     * @return string[]
     */
    public function getMonitoredFolderList(): array;

    /**
     * Gen an ID.
     */
    public function getId(): ?string;

    /**
     * Get an entity type.
     */
    public function getEntityType(): string;

    /**
     * Get IMAP params.
     */
    public function getImapParams(): ?ImapParams;

    /**
     * @return ?class-string<object>
     */
    public function getImapHandlerClassName(): ?string;

    /**
     * Get a SENT folder.
     */
    public function getSentFolder(): ?string;

    /**
     * Is available for sending.
     */
    public function isAvailableForSending(): bool;

    /**
     * Get SMTP params.
     */
    public function getSmtpParams(): ?SmtpParams;

    /**
     * Store sent emails on IMAP.
     */
    public function storeSentEmails(): bool;

    /**
     * Get the last connection time;
     */
    public function getConnectedAt(): ?DateTime;


    /**
     * Get an email folder mapped to the email folder.
     *
     * @since 9.3.0
     */
    public function getMappedEmailFolder(string $folder): ?Link;
}
