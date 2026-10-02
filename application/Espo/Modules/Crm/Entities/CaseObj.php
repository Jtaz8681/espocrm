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

use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Entities\Attachment;
use Espo\Entities\User;
use Espo\ORM\EntityCollection;

class CaseObj extends Entity
{
    public const ENTITY_TYPE = 'Case';

    public const STATUS_NEW = 'New';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_DUPLICATE = 'Duplicate';

    /**
     * @since 10.0.8
     */
    public const string FIELD_ATTACHMENTS = 'attachments';

    protected $entityType = 'Case';

    public function getNumber(): ?int
    {
        return $this->get('number');
    }

    public function setName(?string $name): self
    {
        return $this->set(Field::NAME, $name);
    }

    public function setDescription(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function getDescription(): ?string
    {
        return $this->get('description');
    }

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function setStatus(string $status): self
    {
        return $this->set('status', $status);
    }

    public function getInboundEmailId(): ?string
    {
        return $this->get('inboundEmailId');
    }

    public function getAccount(): ?Account
    {
        /** @var ?Account */
        return $this->relations->getOne('account');
    }

    /**
     * A primary contact.
     */
    public function getContact(): ?Contact
    {
        /** @var ?Contact */
        return $this->relations->getOne('contact');
    }

    public function getContacts(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('contacts');
    }

    public function getLead(): ?Lead
    {
        /** @var ?Lead */
        return $this->relations->getOne('lead');
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

    /**
     * @return string[]
     */
    public function getAttachmentIdList(): array
    {
        /** @var string[] */
        return $this->getLinkMultipleIdList('attachments');
    }

    /**
     * @return EntityCollection<Attachment>
     */
    public function getAttachments(): EntityCollection
    {
        /** @var EntityCollection<Attachment> */
        return $this->relations->getMany('attachments');
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

    public function setAccount(Account|Link|null $account): self
    {
        return $this->setRelatedLinkOrEntity('account', $account);
    }

    public function setContact(Contact|Link|null $contact): self
    {
        return $this->setRelatedLinkOrEntity('contact', $contact);
    }

    /**
     * @since 9.0.0
     */
    public function isInternal(): bool
    {
        return (bool) $this->get('isInternal');
    }

    /**
     * @since 9.0.0
     */
    public function setIsInternal(bool $isInternal): self
    {
        return $this->set('isInternal', $isInternal);
    }
}
