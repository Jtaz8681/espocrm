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
use RuntimeException;

class Document extends Entity
{
    public const ENTITY_TYPE = 'Document';

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_DRAFT = 'Draft';

    /**
     * @since 10.0.8
     */
    public const string FIELD_FILE = 'file';

    /**
     * @since 10.0.8
     */
    public const string FIELD_PUBLISH_DATE = 'publishDate';

    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    public function setFile(?Attachment $file): self
    {
        $this->relations->set('file', $file);

        return $this;
    }

    public function getFileId(): ?string
    {
        return $this->get('fileId');
    }

    public function getFile(): ?Attachment
    {
        $file = $this->relations->getOne('file');

        if ($file && !$file instanceof Attachment) {
            throw new RuntimeException();
        }

        return $file;
    }

    public function getStatus(): ?string
    {
        return $this->get('status');
    }

    public function setStatus(string $status): self
    {
        return $this->set('status', $status);
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

    public function setAssignedUser(Link|User|null $assignedUser): self
    {
        return $this->setRelatedLinkOrEntity(Field::ASSIGNED_USER, $assignedUser);
    }

    public function setTeams(LinkMultiple $teams): self
    {
        $this->setValueObject(Field::TEAMS, $teams);

        return $this;
    }
}
