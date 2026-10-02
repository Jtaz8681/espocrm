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

namespace Espo\Core\Mail\Importer;

use Espo\Entities\EmailFilter;

/**
 * Immutable.
 */
class Data
{
    private ?string $assignedUserId = null;
    /** @var string[] */
    private array $teamIdList = [];
    /** @var string[] */
    private array $userIdList = [];
    /** @var iterable<EmailFilter> */
    private iterable $filterList = [];
    private bool $fetchOnlyHeader = false;
    /** @var array<string, string> */
    private array $folderData = [];
    private ?string $groupEmailFolderId = null;

    public static function create(): self
    {
        return new self();
    }

    public function getAssignedUserId(): ?string
    {
        return $this->assignedUserId;
    }

    /**
     * @return string[]
     */
    public function getTeamIdList(): array
    {
        return $this->teamIdList;
    }

    /**
     * @return string[]
     */
    public function getUserIdList(): array
    {
        return $this->userIdList;
    }

    /**
     * @return iterable<EmailFilter>
     */
    public function getFilterList(): iterable
    {
        return $this->filterList;
    }

    public function fetchOnlyHeader(): bool
    {
        return $this->fetchOnlyHeader;
    }

    /**
     * @return array<string, string>
     */
    public function getFolderData(): array
    {
        return $this->folderData;
    }

    public function getGroupEmailFolderId(): ?string
    {
        return $this->groupEmailFolderId;
    }

    public function withAssignedUserId(?string $assignedUserId): self
    {
        $obj = clone $this;

        $obj->assignedUserId = $assignedUserId;

        return $obj;
    }

    /**
     * @param string[] $teamIdList
     */
    public function withTeamIdList(array $teamIdList): self
    {
        $obj = clone $this;

        $obj->teamIdList = $teamIdList;

        return $obj;
    }

    /**
     * @param string[] $userIdList
     */
    public function withUserIdList(array $userIdList): self
    {
        $obj = clone $this;
        $obj->userIdList = $userIdList;

        return $obj;
    }

    /**
     * @param iterable<EmailFilter> $filterList
     */
    public function withFilterList(iterable $filterList): self
    {
        $obj = clone $this;
        $obj->filterList = $filterList;

        return $obj;
    }

    public function withFetchOnlyHeader(bool $fetchOnlyHeader = true): self
    {
        $obj = clone $this;
        $obj->fetchOnlyHeader = $fetchOnlyHeader;

        return $obj;
    }

    /**
     * @param array<string, string> $folderData
     */
    public function withFolderData(array $folderData): self
    {
        $obj = clone $this;
        $obj->folderData = $folderData;

        return $obj;
    }

    public function withGroupEmailFolderId(?string $groupEmailFolderId): self
    {
        $obj = clone $this;
        $obj->groupEmailFolderId = $groupEmailFolderId;

        return $obj;
    }
}
