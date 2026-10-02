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

namespace Espo\Classes\Select\Email\AccessControlFilters;

use Espo\Core\Select\AccessControl\Filter;
use Espo\Classes\Select\Email\Helpers\JoinHelper;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

class PortalOnlyAccount implements Filter
{
    public function __construct(private User $user, private JoinHelper $joinHelper)
    {}

    public function apply(QueryBuilder $queryBuilder): void
    {
        $this->joinHelper->joinEmailUser($queryBuilder, $this->user->getId());

        $orGroup = [
            Email::ALIAS_INBOX . '.userId' => $this->user->getId(),
        ];

        $accountIdList = $this->user->getAccounts()->getIdList();

        if (count($accountIdList)) {
            $orGroup['accountId'] = $accountIdList;
        }

        $contactId = $this->user->getContactId();

        if ($contactId) {
            $orGroup[] = [
                'parentId' => $contactId,
                'parentType' => Contact::ENTITY_TYPE,
            ];
        }

        $queryBuilder->where(['OR' => $orGroup]);
    }
}
