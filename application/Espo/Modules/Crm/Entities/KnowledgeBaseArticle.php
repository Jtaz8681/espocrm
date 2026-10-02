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
use Espo\ORM\Collection;

class KnowledgeBaseArticle extends Entity
{
    public const ENTITY_TYPE = 'KnowledgeBaseArticle';

    public const STATUS_PUBLISHED = 'Published';
    public const STATUS_ARCHIVED = 'Archived';

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function setName(?string $name): self
    {
        $this->set(Field::NAME, $name);

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

    public function getStatus(): string
    {
        return (string) $this->get('status');
    }

    public function getOrder(): ?int
    {
        return $this->get('order');
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
     * @return iterable<Attachment>
     */
    public function getAttachments(): iterable
    {
        /** @var Collection<Attachment> */
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

    public function getBody(): ?string
    {
        return $this->get('body');
    }

    public function setBody(?string $body): self
    {
        return $this->set('body', $body);
    }
}
