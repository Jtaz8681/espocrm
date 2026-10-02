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

use Espo\Core\Entities\Person;
use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Name\Field;
use Espo\Modules\Crm\Entities\Contact;
use RuntimeException;

class User extends Person
{
    public const ENTITY_TYPE = 'User';

    public const ATTR_TYPE = 'type';
    public const ATTR_IS_ACTIVE = 'isActive';

    /** @since 10.0.0 */
    public const string FIELD_USER_NAME = 'userName';
    /** @since 10.0.0 */
    public const string FIELD_PASSWORD = 'password';
    /** @since 10.0.0 */
    public const string FIELD_PASSWORD_VERSION = 'passwordVersion';
    /** @since 10.0.0 */
    public const string FIELD_TYPE = 'type';
    /** @since 10.0.4 */
    public const string FIELD_API_KEY = 'apiKey';
    /** @since 10.0.4 */
    public const string FIELD_AUTH_METHOD = 'authMethod';

    public const LINK_ACCOUNTS = 'accounts';
    public const LINK_CONTACT = 'contact';
    public const LINK_PORTALS = 'portals';
    public const LINK_TEAMS = 'teams';
    public const LINK_DEFAULT_TEAM = 'defaultTeam';
    public const LINK_ROLES = 'roles';
    public const LINK_PORTAL_ROLES = 'portalRoles';

    public const TYPE_PORTAL = 'portal';
    public const TYPE_ADMIN = 'admin';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_REGULAR = 'regular';
    public const TYPE_API = 'api';
    public const TYPE_SUPER_ADMIN = 'super-admin';

    public const RELATIONSHIP_ENTITY_USER = 'EntityUser';
    public const RELATIONSHIP_ENTITY_COLLABORATOR = 'EntityCollaborator';

    /** @since 10.0.4 */
    public const string AUTH_METHOD_API_KEY = 'ApiKey';

    public function get(string $attribute): mixed
    {
        if ($attribute === Field::NAME) {
            return $this->getNameInternal();
        }

        return parent::get($attribute);
    }

    public function has(string $attribute): bool
    {
        if ($attribute === Field::NAME) {
            return $this->hasNameInternal();
        }

        return parent::has($attribute);
    }

    public function isActive(): bool
    {
        return (bool) $this->get('isActive');
    }

    /**
     * @deprecated Use `isPortal`.
     */
    public function isPortalUser(): bool
    {
        return $this->isPortal();
    }

    public function getType(): ?string
    {
        return $this->get('type');
    }

    /**
     * @param self::TYPE_* $type
     */
    public function setType(string $type): self
    {
        return $this->set('type', $type);
    }

    public function setUserName(?string $userName): self
    {
        return $this->set('userName', $userName);
    }

    /**
     * Is regular user.
     */
    public function isRegular(): bool
    {
        return $this->getType() === self::TYPE_REGULAR ||
            ($this->has('type') && !$this->getType());
    }

    /**
     * Is admin, super-admin or system user.
     */
    public function isAdmin(): bool
    {
        return $this->getType() === self::TYPE_ADMIN ||
            $this->isSystem() ||
            $this->isSuperAdmin();
    }

    /**
     * Is portal user.
     */
    public function isPortal(): bool
    {
        return $this->getType() === self::TYPE_PORTAL;
    }

    /**
     * Is API user.
     */
    public function isApi(): bool
    {
        return $this->getType() === self::TYPE_API;
    }

    /**
     * Is system user.
     */
    public function isSystem(): bool
    {
        return $this->getType() === self::TYPE_SYSTEM;
    }

    /**
     * Is super-admin user.
     */
    public function isSuperAdmin(): bool
    {
        return $this->getType() === self::TYPE_SUPER_ADMIN;
    }

    public function getRoles(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('roles');
    }

    public function getDefaultTeam(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('defaultTeam');
    }

    public function getWorkingTimeCalendar(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('workingTimeCalendar');
    }

    public function getLayoutSet(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('layoutSet');
    }

    public function getTeams(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('teams');
    }

    /**
     * @return string[]
     */
    public function getTeamIdList(): array
    {
        /** @var string[] */
        return $this->getLinkMultipleIdList('teams');
    }

    public function setDefaultTeam(?Link $defaultTeam): self
    {
        $this->setValueObject('defaultTeam', $defaultTeam);

        return $this;
    }

    public function setTeams(LinkMultiple $teams): self
    {
        $this->setValueObject('teams', $teams);

        return $this;
    }

    public function getPortals(): LinkMultiple
    {
        /** @var LinkMultiple */
        return $this->getValueObject('portals');
    }

    public function setPortals(LinkMultiple $portals): self
    {
        $this->setValueObject('portals', $portals);

        return $this;
    }

    public function setRoles(LinkMultiple $roles): self
    {
        $this->setValueObject('roles', $roles);

        return $this;
    }

    public function loadAccountField(): void
    {
        if (!$this->entityManager) {
            throw new RuntimeException("No entity manager");
        }

        if ($this->get('contactId')) {
            $contact = $this->entityManager->getEntityById(Contact::ENTITY_TYPE, $this->get('contactId'));

            if ($contact && $contact->get('accountId')) {
                $this->set('accountId', $contact->get('accountId'));
                $this->set('accountName', $contact->get('accountName'));
            }
        }
    }

    public function setTitle(?string $title): self
    {
        $this->set('title', $title);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->get('title');
    }

    public function getUserName(): ?string
    {
        return $this->get('userName');
    }

    public function getAuthMethod(): ?string
    {
        return $this->get('authMethod');
    }

    public function getContactId(): ?string
    {
        return $this->get('contactId');
    }

    public function getContact(): ?Contact
    {
        /** @var ?Contact */
        return $this->relations->getOne('contact');
    }

    /**
     * Get a portal ID of the currently logged user.
     */
    public function getPortalId(): ?string
    {
        return $this->get('portalId');
    }

    public function getAccounts(): LinkMultiple
    {
        /** @var LinkMultiple $value */
        $value = $this->getValueObject('accounts');

        return $value;
    }

    public function getAvatarId(): ?string
    {
        return $this->get('avatarId');
    }

    public function getAvatarColor(): ?string
    {
        return $this->get('avatarColor');
    }

    /**
     * Get a password hash.
     */
    public function getPassword(): string
    {
        return $this->get(self::FIELD_PASSWORD) ?? '';
    }

    private function getNameInternal(): ?string
    {
        if (!$this->hasInContainer(Field::NAME) || !$this->getFromContainer(Field::NAME)) {
            if ($this->get('userName')) {
                return $this->get('userName');
            }
        }

        return $this->getFromContainer(Field::NAME);
    }

    private function hasNameInternal(): bool
    {
        if ($this->hasInContainer(Field::NAME)) {
            return true;
        }

        if ($this->has('userName')) {
            return true;
        }

        return false;
    }

    /**
     * @since 10.0.0
     * @internal
     */
    public function getPasswordVersion(): ?int
    {
        return $this->get(self::FIELD_PASSWORD_VERSION);
    }
}
