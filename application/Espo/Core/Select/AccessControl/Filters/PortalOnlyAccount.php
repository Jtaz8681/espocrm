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

namespace Espo\Core\Select\AccessControl\Filters;

use Espo\Core\Name\Field;
use Espo\Core\Portal\Acl\OwnershipChecker\MetadataProvider;
use Espo\Core\Select\AccessControl\Filter;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Core\Select\Helpers\RelationQueryHelper;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem;
use Espo\ORM\Query\SelectBuilder;

class PortalOnlyAccount implements Filter
{
    public function __construct(
        private string $entityType,
        private User $user,
        private FieldHelper $fieldHelper,
        private MetadataProvider $metadataProvider,
        private RelationQueryHelper $relationQueryHelper,
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $orBuilder = OrGroup::createBuilder();

        $accountIds = $this->user->getAccounts()->getIdList();
        $contactId = $this->user->getContactId();

        if ($accountIds !== []) {
            $or = $this->prepareAccountWhere($queryBuilder, $accountIds);

            if ($or) {
                $orBuilder->add($or);
            }
        }

        if ($contactId) {
            $or = $this->prepareContactWhere($queryBuilder, $contactId);

            if ($or) {
                $orBuilder->add($or);
            }
        }

        if ($this->fieldHelper->hasCreatedByField()) {
            $orBuilder->add(
                WhereClause::fromRaw([Field::CREATED_BY . 'Id' => $this->user->getId()])
            );
        }

        $orGroup = $orBuilder->build();

        if ($orGroup->getItemCount() === 0) {
            $queryBuilder->where([Attribute::ID => null]);

            return;
        }

        $queryBuilder->where($orGroup);
    }

    /**
     * @param string[] $ids
     */
    private function prepareAccountWhere(SelectBuilder $queryBuilder, array $ids): ?WhereItem
    {
        $defs = $this->metadataProvider->getAccountLink($this->entityType);

        if (!$defs) {
            return null;
        }

        return $this->relationQueryHelper->prepareLinkWhere($defs, Account::ENTITY_TYPE, $ids, $queryBuilder);
    }

    private function prepareContactWhere(SelectBuilder $queryBuilder, string $id): ?WhereItem
    {
        $defs = $this->metadataProvider->getContactLink($this->entityType);

        if (!$defs) {
            return null;
        }

        return $this->relationQueryHelper->prepareLinkWhere($defs, Contact::ENTITY_TYPE, $id, $queryBuilder);
    }
}
