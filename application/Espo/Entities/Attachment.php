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

namespace Espo\Entities;

use Espo\Core\Field\Link;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;

use Espo\Core\Field\LinkParent;

class Attachment extends Entity
{
    public const ENTITY_TYPE = 'Attachment';

    public const ROLE_ATTACHMENT = 'Attachment';
    public const ROLE_INLINE_ATTACHMENT = 'Inline Attachment';
    public const ROLE_EXPORT_FILE = 'Export File';

    /**
     * Multiple attachment can refer to one file. Source ID is an original attachment.
     */
    public function getSourceId(): ?string
    {
        $sourceId = $this->get('sourceId');

        if (!$sourceId && $this->hasId()) {
            $sourceId = $this->getId();
        }

        return $sourceId;
    }

    /**
     * A storage.
     */
    public function getStorage(): ?string
    {
        return $this->get('storage');
    }

    /**
     * A file name.
     */
    public function getName(): ?string
    {
        return $this->get(Field::NAME);
    }

    /**
     * A size in bytes.
     */
    public function getSize(): ?int
    {
        return $this->get('size');
    }

    /**
     * A mime-type.
     */
    public function getType(): ?string
    {
        return $this->get('type');
    }

    /**
     * A field the attachment is related through.
     */
    public function getTargetField(): ?string
    {
        return $this->get('field');
    }

    public function getParent(): ?LinkParent
    {
        /** @var ?LinkParent */
        return $this->getValueObject(Field::PARENT);
    }

    public function getRelated(): ?LinkParent
    {
        /** @var ?LinkParent */
        return $this->getValueObject('related');
    }

    public function getParentType(): ?string
    {
        return $this->get('parentType');
    }

    /**
     * @since 10.0.8
     */
    public function getParentId(): ?string
    {
        return $this->get('parentId');
    }

    public function getRelatedType(): ?string
    {
        return $this->get('relatedType');
    }

    /**
     * @since 10.0.8
     */
    public function getRelatedId(): ?string
    {
        return $this->get('relatedId');
    }

    public function isBeingUploaded(): bool
    {
        return (bool) $this->get('isBeingUploaded');
    }

    /**
     * A role.
     */
    public function getRole(): ?string
    {
        return $this->get('role');
    }

    /**
     * Multiple attachment can refer to one file. Source ID is an original attachment.
     */
    public function setSourceId(?string $sourceId): self
    {
        $this->set('sourceId', $sourceId);

        return $this;
    }

    public function setStorage(?string $storage): self
    {
        $this->set('storage', $storage);

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->set(Field::NAME, $name);

        return $this;
    }

    public function setType(?string $type): self
    {
        $this->set('type', $type);

        return $this;
    }

    public function setRole(?string $type): self
    {
        $this->set('role', $type);

        return $this;
    }

    public function setSize(?int $size): self
    {
        $this->set('size', $size);

        return $this;
    }

    public function setContents(?string $contents): self
    {
        $this->set('contents', $contents);

        return $this;
    }

    public function setTargetField(?string $field): self
    {
        $this->set('field', $field);

        return $this;
    }

    public function setParent(LinkParent|Entity|null $parent): self
    {
        if ($parent instanceof LinkParent) {
            $this->setValueObject(Field::PARENT, $parent);

            return $this;
        }

        $this->relations->set(Field::PARENT, $parent);

        return $this;
    }

    public function setRelated(LinkParent|Entity|null $related): self
    {
        if ($related instanceof LinkParent) {
            $this->setValueObject('related', $related);

            return $this;
        }

        $this->relations->set('related', $related);

        return $this;
    }

    /**
     * @since 9.3.5
     */
    public function getCreatedBy(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject(Field::CREATED_BY);
    }
}
