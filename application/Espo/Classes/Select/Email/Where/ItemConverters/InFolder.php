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

namespace Espo\Classes\Select\Email\Where\ItemConverters;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Name\Link;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Core\Select\Where\ItemConverter;
use Espo\Core\Select\Where\Item;

use Espo\Entities\Email;
use Espo\Entities\GroupEmailFolder;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use Espo\ORM\Query\Part\WhereItem as WhereClauseItem;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Classes\Select\Email\Helpers\JoinHelper;
use Espo\Tools\Email\Folder;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class InFolder implements ItemConverter
{
    public function __construct(
        private User $user,
        private EntityManager $entityManager,
        private JoinHelper $joinHelper,
        private SelectBuilderFactory $selectBuilderFactory
    ) {}

    public function convert(QueryBuilder $queryBuilder, Item $item): WhereClauseItem
    {
        $folderId = $item->getValue();

        return match ($folderId) {
            Folder::ALL => WhereClause::fromRaw([]),
            Folder::INBOX => $this->convertInbox($queryBuilder),
            Folder::IMPORTANT => $this->convertImportant($queryBuilder),
            Folder::SENT => $this->convertSent($queryBuilder),
            Folder::TRASH => $this->convertTrash($queryBuilder),
            Folder::ARCHIVE => $this->convertArchive($queryBuilder),
            Folder::DRAFTS => $this->convertDraft(),
            default => $this->convertFolderId($queryBuilder, $folderId),
        };
    }

    private function convertInbox(QueryBuilder $queryBuilder): WhereClauseItem
    {
        $this->joinEmailUser($queryBuilder);

        $groupEmailFoldersIds = $this->getUserGroupEmailFoldersIds();

        $whereClause = [
            Email::ALIAS_INBOX . '.inTrash' => false,
            Email::ALIAS_INBOX . '.inArchive' => false,
            Email::ALIAS_INBOX . '.folderId' => null,
            Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
            [
                'status' => [
                    Email::STATUS_ARCHIVED,
                    Email::STATUS_SENT,
                ],
                'OR' => [
                    'groupFolderId' => null,
                    'groupFolderId!=' => $groupEmailFoldersIds,
                ]
            ],
        ];

        $emailAddressIdList = $this->getEmailAddressIdList();

        if ($emailAddressIdList !== []) {
            $whereClause['fromEmailAddressId!='] = $emailAddressIdList;

            $whereClause[] = [
                'OR' => [
                    'status' => Email::STATUS_ARCHIVED,
                    'createdById!=' => $this->user->getId(),
                ],
            ];
        } else {
            $whereClause[] = [
                'status' => Email::STATUS_ARCHIVED,
                'createdById!=' => $this->user->getId(),
            ];
        }

        return WhereClause::fromRaw($whereClause);
    }

    private function convertSent(QueryBuilder $queryBuilder): WhereClauseItem
    {
        $this->joinEmailUser($queryBuilder);

        return WhereClause::fromRaw([
            'OR' => [
                'fromEmailAddressId' => $this->getEmailAddressIdList(),
                [
                    'status' => Email::STATUS_SENT,
                    'createdById' => $this->user->getId(),
                ]
            ],
            [
                'status!=' => Email::STATUS_DRAFT,
            ],
            Email::ALIAS_INBOX . '.inTrash' => false,
            [
                'OR' => [
                    'groupFolderId' => null,
                    'groupFolderId!=' => Email::GROUP_STATUS_FOLDER_TRASH,
                ]
            ]
        ]);
    }

    private function convertImportant(QueryBuilder $queryBuilder): WhereClauseItem
    {
        $this->joinEmailUser($queryBuilder);

        return WhereClause::fromRaw([
            Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
            Email::ALIAS_INBOX . '.isImportant' => true,
        ]);
    }

    private function convertTrash(QueryBuilder $queryBuilder): WhereClauseItem
    {
        $this->joinEmailUser($queryBuilder);

        return WhereClause::fromRaw([
            'OR' => [
                [
                    Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
                    Email::ALIAS_INBOX . '.inTrash' => true,
                ],
                [
                    'groupFolderId!=' => null,
                    'groupStatusFolder' => Email::GROUP_STATUS_FOLDER_TRASH,
                ],
            ]
        ]);
    }

    private function convertArchive(QueryBuilder $queryBuilder): WhereClauseItem
    {
        $this->joinEmailUser($queryBuilder);

        return WhereClause::fromRaw([
            'OR' => [
                [
                    Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
                    Email::ALIAS_INBOX . '.inArchive' => true,
                ],
                [
                    'groupFolderId!=' => null,
                    'groupStatusFolder' => Email::GROUP_STATUS_FOLDER_ARCHIVE,
                ],
            ]
        ]);
    }

    private function convertDraft(): WhereClauseItem
    {
        return WhereClause::fromRaw([
            'status' => Email::STATUS_DRAFT,
            'createdById' => $this->user->getId(),
        ]);
    }

    private function convertFolderId(QueryBuilder $queryBuilder, string $folderId): WhereClauseItem
    {
        $this->joinEmailUser($queryBuilder);

        if (str_starts_with($folderId, 'group:')) {
            $groupFolderId = substr($folderId, 6);

            if ($groupFolderId === '') {
                $groupFolderId = null;
            }

            return WhereClause::fromRaw([
                'groupFolderId' => $groupFolderId,
                'groupStatusFolder' => null,
                'fromEmailAddressId!=' => $this->getEmailAddressIdList(),
                'status' => [
                    Email::STATUS_ARCHIVED,
                    Email::STATUS_SENT,
                ],
                'OR' => [
                    'status' => Email::STATUS_ARCHIVED,
                    'createdById!=' => $this->user->getId(),
                ],
            ]);
        }

        return WhereClause::fromRaw([
            Email::ALIAS_INBOX . '.inTrash' => false,
            Email::ALIAS_INBOX . '.inArchive' => false,
            Email::ALIAS_INBOX . '.folderId' => $folderId,
            'groupFolderId' => null,
            'status' => [
                Email::STATUS_ARCHIVED,
                Email::STATUS_SENT,
            ],
        ]);
    }

    protected function joinEmailUser(QueryBuilder $queryBuilder): void
    {
        $this->joinHelper->joinEmailUser($queryBuilder, $this->user->getId());
    }

    /**
     * @return string[]
     */
    protected function getEmailAddressIdList(): array
    {
        $emailAddressList = $this->entityManager
            ->getRelation($this->user, Link::EMAIL_ADDRESSES)
            ->select([Attribute::ID])
            ->find();

        $emailAddressIdList = [];

        foreach ($emailAddressList as $emailAddress) {
            $emailAddressIdList[] = $emailAddress->getId();
        }

        return $emailAddressIdList;
    }

    /**
     * @return string[]
     */
    private function getUserGroupEmailFoldersIds(): array
    {
        $selectBuilder = $this->selectBuilderFactory
            ->create()
            ->forUser($this->user)
            ->from(GroupEmailFolder::ENTITY_TYPE)
            ->withAccessControlFilter();

        try {
            $groupEmailFolders = $this->entityManager
                ->getRDBRepositoryByClass(GroupEmailFolder::class)
                ->clone($selectBuilder->build())
                ->select([Attribute::ID])
                ->find();
        } catch (BadRequest|Forbidden $e) {
            throw new RuntimeException('', 0, $e);
        }

        $groupEmailFoldersIds = [];

        foreach ($groupEmailFolders as $groupEmailFolder) {
            $groupEmailFoldersIds[] = $groupEmailFolder->getId();
        }

        return $groupEmailFoldersIds;
    }
}
