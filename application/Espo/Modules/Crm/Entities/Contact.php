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

use Espo\Core\Entities\Person;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Entities\User;
use Espo\ORM\EntityCollection;

class Contact extends Person
{
    public const ENTITY_TYPE = 'Contact';

    /** @since v9.3.0. */
    public const string ATTR_ACCOUNT_ID = 'accountId';
    /** @since v9.3.0. */
    public const string FIELD_ACCOUNTS = 'accounts';
    /** @since v9.3.0. */
    public const string FIELD_ACCOUNT = 'account';

    /** @since v9.3.0. */
    public const string RELATIONSHIP_ACCOUNT_CONTACT = 'AccountContact';

    /** @since v9.3.0. */
    public const string COLUMN_ACCOUNTS_ROLE = 'role';

    /**
     * An assigned user.
     */
    public function getAssignedUser(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::ASSIGNED_USER);
    }

    /**
     * A primary account.
     */
    public function getAccount(): ?Account
    {
        /** @var ?Account */
        return $this->relations->getOne('account');
    }

    /**
     * Get accounts as link-multiple.
     *
     * @since 10.0.0
     */
    public function getAccountsLinkMultiple(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('accounts');
    }

    /**
     * Set accounts.
     *
     * @since 10.0.0
     */
    public function setAccounts(LinkMultiple $accounts): self
    {
        return $this->setValueObject(self::FIELD_ACCOUNTS, $accounts);
    }

    /**
     * Get accounts.
     *
     * @return EntityCollection<Account>
     */
    public function getAccounts(): EntityCollection
    {
        /** @var EntityCollection<Account> */
        return $this->relations->getMany('accounts');
    }

    /**
     * Set a primary account.
     */
    public function setAccount(Account|Link|null $account): self
    {
        return $this->setRelatedLinkOrEntity('account', $account);
    }

    /**
     * Teams.
     */
    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(Field::TEAMS);
    }

    /**
     * A title (for a primary account).
     */
    public function getTitle(): ?string
    {
        return $this->get('title');
    }

    public function setAssignedUser(Link|User|null $assignedUser): self
    {
        return $this->setRelatedLinkOrEntity(Field::ASSIGNED_USER, $assignedUser);
    }

    public function setTeams(LinkMultiple $teams): self
    {
        $this->setValueObject(Field::TEAMS, $teams);

        return $this;
    }

    public function setDescription(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function getDescription(): ?string
    {
        return $this->get('description');
    }
}
