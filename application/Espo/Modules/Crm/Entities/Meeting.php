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

use Espo\Core\Field\DateTimeOptional;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Entities\User;
use Espo\ORM\Entity as OrmEntity;

class Meeting extends Entity
{
    public const ENTITY_TYPE = 'Meeting';

    public const ATTENDEE_STATUS_NONE = 'None';
    public const ATTENDEE_STATUS_ACCEPTED = 'Accepted';
    public const ATTENDEE_STATUS_TENTATIVE = 'Tentative';
    public const ATTENDEE_STATUS_DECLINED = 'Declined';

    public const STATUS_PLANNED = 'Planned';
    public const STATUS_HELD = 'Held';
    public const STATUS_NOT_HELD = 'Not Held';

    /** @since 10.0.0 */
    public const string FIELD_IS_ALL_DAY = 'isAllDay';
    /** @since 10.0.0 */
    public const string FIELD_DATE_START_DATE = 'dateStartDate';
    /** @since 10.0.0 */
    public const string FIELD_DATE_END_DATE = 'dateEndDate';
    /** @since 10.0.0 */
    public const string FIELD_DATE_START = 'dateStart';
    /** @since 10.0.0 */
    public const string FIELD_DATE_END = 'dateEnd';
    /** @since 10.0.0 */
    public const string FIELD_STATUS = 'status';

    public const LINK_USERS = 'users';
    public const LINK_CONTACTS = 'contacts';
    public const LINK_LEADS = 'leads';

    public function setName(?string $name): self
    {
        return $this->set(Field::NAME, $name);
    }

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function setDescription(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function getDescription(): ?string
    {
        return $this->get('description');
    }

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function setStatus(string $status): self
    {
        return $this->set('status', $status);
    }

    public function getDateStart(): ?DateTimeOptional
    {
        /** @var ?DateTimeOptional */
        return $this->getValueObject('dateStart');
    }

    public function setDateStart(?DateTimeOptional $dateStart): self
    {
        $this->setValueObject('dateStart', $dateStart);

        return $this;
    }

    public function getDateEnd(): ?DateTimeOptional
    {
        /** @var ?DateTimeOptional */
        return $this->getValueObject('dateEnd');
    }

    public function setDateEnd(?DateTimeOptional $dateEnd): self
    {
        $this->setValueObject('dateEnd', $dateEnd);

        return $this;
    }

    /**
     * @param int $duration Duration in seconds.
     * @since 10.0.6
     */
    public function setDuration(int $duration): self
    {
        return $this->set('duration', $duration);
    }

    public function setAssignedUserId(?string $assignedUserId): self
    {
        $this->set('assignedUserId', $assignedUserId);

        return $this;
    }

    public function getCreatedBy(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::CREATED_BY);
    }

    public function getModifiedBy(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::MODIFIED_BY);
    }

    public function getAssignedUser(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::ASSIGNED_USER);
    }

    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(Field::TEAMS);
    }

    public function getUsers(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(self::LINK_USERS);
    }

    public function getContacts(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(self::LINK_CONTACTS);
    }

    public function getLeads(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject(self::LINK_LEADS);
    }

    public function setUsers(LinkMultiple $users): self
    {
        return $this->setValueObject(self::LINK_USERS, $users);
    }

    public function setContacts(LinkMultiple $contacts): self
    {
        return $this->setValueObject(self::LINK_CONTACTS, $contacts);
    }

    public function setLeads(LinkMultiple $leads): self
    {
        return $this->setValueObject(self::LINK_LEADS, $leads);
    }

    public function setParent(Entity|LinkParent|null $parent): self
    {
        if ($parent instanceof LinkParent) {
            $this->setValueObject(Field::PARENT, $parent);

            return $this;
        }

        $this->relations->set(Field::PARENT, $parent);

        return $this;
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

    public function setAccount(Link|Account|null $account): self
    {
        return $this->setRelatedLinkOrEntity('account', $account);
    }

    public function getAccount(): ?Account
    {
        /** @var ?Account */
        return $this->relations->getOne('account');
    }

    public function getParent(): ?OrmEntity
    {
        return $this->relations->getOne(Field::PARENT);
    }

    /**
     * @since 9.0.0
     */
    public function getUid(): ?string
    {
        return $this->get('uid');
    }

    /**
     * @since 9.0.0
     */
    public function setUid(?string $uid): self
    {
        return $this->set('uid', $uid);
    }

    /**
     * @since 9.0.0
     */
    public function setJoinUrl(?string $joinUrl): self
    {
        return $this->set('joinUrl', $joinUrl);
    }

    /**
     * @since 9.0.0
     */
    public function getJoinUrl(): ?string
    {
        return $this->get('joinUrl');
    }

    /**
     * @since 10.0.0
     */
    public function getExternalService(): ?string
    {
        return $this->get('externalService');
    }

    /**
     * @since 10.0.0
     */
    public function isAllDay(): bool
    {
        return (bool) $this->get(self::FIELD_IS_ALL_DAY);
    }

    /**
     * @since 10.0.0
     */
    public function setIsAllDay(bool $isAllDay): self
    {
        return $this->set(self::FIELD_IS_ALL_DAY, $isAllDay);
    }
}
