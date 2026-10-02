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

namespace Espo\Classes\Select\Email\AdditionalAppliers;

use Espo\Core\Select\Applier\AdditionalApplier;
use Espo\Core\Select\Primary\Filters\One;
use Espo\Core\Select\SearchParams;
use Espo\Classes\Select\Email\Helpers\JoinHelper;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder;
use Espo\Tools\Email\Folder;

class Main implements AdditionalApplier
{
    public function __construct(
        private User $user,
        private JoinHelper $joinHelper
    ) {}

    public function apply(SelectBuilder $queryBuilder, SearchParams $searchParams): void
    {
        $folder = $this->retrieveFolder($searchParams);

        $this->applyIndexes($folder, $queryBuilder, $searchParams);

        if ($folder !== Folder::DRAFTS) {
            $this->joinEmailUser($queryBuilder);
        }
    }

    private function applyIndexes(?string $folder, SelectBuilder $queryBuilder, SearchParams $searchParams): void
    {
        if ($searchParams->getPrimaryFilter() === One::NAME) {
            return;
        }

        if ($searchParams->getTextFilter()) {
            return;
        }

        if ($folder === Folder::IMPORTANT) {
            return;
        }

        if ($folder === Folder::DRAFTS) {
            $queryBuilder->useIndex('createdById');

            return;
        }

        /*if ($this->checkApplyDateSentIndex($queryBuilder, $searchParams)) {
            $queryBuilder->useIndex('dateSent');
        }*/
    }

    private function joinEmailUser(SelectBuilder $queryBuilder): void
    {
        $this->joinHelper->joinEmailUser($queryBuilder, $this->user->getId());

        if ($queryBuilder->build()->getSelect() === []) {
            $queryBuilder->select('*');
        }

        $itemList = [
            Email::USERS_COLUMN_IS_READ,
            Email::USERS_COLUMN_IS_IMPORTANT,
            Email::USERS_COLUMN_IN_TRASH,
            Email::USERS_COLUMN_IN_ARCHIVE,
            Email::USERS_COLUMN_FOLDER_ID,
        ];

        foreach ($itemList as $item) {
            $queryBuilder->select(Email::ALIAS_INBOX . '.' . $item, $item);
        }
    }

    private function retrieveFolder(SearchParams $searchParams): ?string
    {
        if (!$searchParams->getWhere()) {
            return null;
        }

        foreach ($searchParams->getWhere()->getItemList() as $item) {
            if ($item->getType() === 'inFolder') {
                return $item->getValue();
            }
        }

        return null;
    }

    /*private function checkApplyDateSentIndex(SelectBuilder $queryBuilder, SearchParams $searchParams): bool
    {
        if ($searchParams->getTextFilter()) {
            return false;
        }

        if ($searchParams->getOrderBy() && $searchParams->getOrderBy() !== 'dateSent') {
            return false;
        }

        $whereItemList = [];

        if ($searchParams->getWhere()) {
            $whereItemList = $searchParams->getWhere()->getItemList();
        }

        foreach ($whereItemList as $item) {
            $itemAttribute = $item->getAttribute();

            if (
                $itemAttribute &&
                $itemAttribute !== 'folderId' &&
                !in_array($itemAttribute, ['teams', 'users', 'status'])
            ) {
                return false;
            }
        }

        if ($queryBuilder->hasJoinAlias('teamsAccess')) {
            return false;
        }

        return true;
    }*/
}
