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

use Espo\Core\Field\DateTime;
use Espo\Core\Mail\Account\Storage\FolderStatus;
use Espo\Core\Mail\Exceptions\ImapError;

interface Storage
{
    /**
     * Mark as unseen.
     *
     * @todo Move to the Message interface. Call from the MessageWrapper.
     *
     * @throws ImapError
     */
    public function unmarkSeen(int $id): void;

    /**
     * Get a message size.
     *
     * @throws ImapError
     */
    public function getSize(int $id): int;

    /**
     * Get message raw content.
     *
     * @throws ImapError
     */
    public function getRawContent(int $id, bool $peek): string;

    /**
     * Get IDs from unique ID.
     *
     * @return int[]
     *
     * @throws ImapError
     */
    public function getUidsFromUid(int $id): array;

    /**
     * Get IDs since a specific date.
     *
     * @return int[]
     *
     * @throws ImapError
     */
    public function getUidsSinceDate(DateTime $since): array;

    /**
     * Get only header and flags. Won't fetch the whole email.
     *
     * @return array{header: string, flags: string[]}
     *
     * @throws ImapError
     */
    public function getHeaderAndFlags(int $id): array;

    /**
     * Close the resource.
     */
    public function close(): void;

    /**
     * @return string[]
     *
     * @throws ImapError
     */
    public function getFolderNames(): array;

    /**
     * Select a folder.
     *
     * @throws ImapError
     */
    public function selectFolder(string $name): void;

    /**
     * Store a message.
     *
     * @throws ImapError
     */
    public function appendMessage(string $content, string $folder): void;

    /**
     * Get folder status. Should be called after the folder is selected.
     *
     * @throws ImapError
     * @since 10.0
     */
    public function getFolderStatus(): FolderStatus;
}
