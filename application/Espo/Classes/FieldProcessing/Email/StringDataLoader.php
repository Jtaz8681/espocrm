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

namespace Espo\Classes\FieldProcessing\Email;

use Espo\Core\Name\Field;
use Espo\Core\Name\Link;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;
use Espo\Repositories\EmailAddress as EmailAddressRepository;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Entities\Email;
use Espo\Entities\User;

/**
 * @implements Loader<Email>
 */
class StringDataLoader implements Loader
{
    private const LINK_EMAIL_ADDRESSES = Link::EMAIL_ADDRESSES;

    /** @var array<string, string> */
    private $fromEmailAddressNameCache = [];

    public function __construct(
        private EntityManager $entityManager,
        private User $user
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        /** @var Email $entity */

        $userEmailAddressIdList = [];

        $emailAddressCollection = $this->entityManager
            ->getRelation($this->user, self::LINK_EMAIL_ADDRESSES)
            ->select([Attribute::ID])
            ->find();

        foreach ($emailAddressCollection as $emailAddress) {
            $userEmailAddressIdList[] = $emailAddress->getId();
        }

        if (
            in_array($entity->get('fromEmailAddressId'), $userEmailAddressIdList) ||
            $entity->get('createdById') === $this->user->getId() &&
            $entity->getStatus() === Email::STATUS_SENT
        ) {
            $entity->loadLinkMultipleField('toEmailAddresses');

            $idList = $entity->get('toEmailAddressesIds');
            $names = $entity->get('toEmailAddressesNames');

            if (empty($idList)) {
                return;
            }

            $list = [];

            foreach ($idList as $emailAddressId) {
                $person = $this->getEmailAddressRepository()->getEntityByAddressId($emailAddressId, null, true);

                $list[] = $person ? $person->get(Field::NAME) : $names->$emailAddressId;
            }

            $entity->set('personStringData', 'To: ' . implode(', ', $list));

            return;
        }

        /**  @var ?string $fromEmailAddressId */
        $fromEmailAddressId = $entity->get('fromEmailAddressId');

        if (!$fromEmailAddressId) {
            return;
        }

        if (!array_key_exists($fromEmailAddressId, $this->fromEmailAddressNameCache)) {
            $person = $this->getEmailAddressRepository()->getEntityByAddressId($fromEmailAddressId, null, true);

            $fromName = $person?->get(Field::NAME);

            $this->fromEmailAddressNameCache[$fromEmailAddressId] = $fromName;
        }

        $fromName =
            $this->fromEmailAddressNameCache[$fromEmailAddressId] ??
            $entity->get('fromName') ??
            $entity->get('fromEmailAddressName');

        $entity->set('personStringData', $fromName);
    }

    private function getEmailAddressRepository(): EmailAddressRepository
    {
        /** @var EmailAddressRepository */
        return $this->entityManager->getRepository('EmailAddress');
    }
}
