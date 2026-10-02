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

namespace Espo\Tools\EmailAddress;

use Espo\Entities\EmailAddress;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Repositories\EmailAddress as EmailAddressRepository;

use RuntimeException;

/**
 * Entity lookup by an email address.
 */
class EntityLookup
{
    private EmailAddressRepository $internalRepository;

    public function __construct(
        private Repository $repository,
        EntityManager $entityManager
    ) {
        $repository = $entityManager->getRDBRepository(EmailAddress::ENTITY_TYPE);

        if (!$repository instanceof EmailAddressRepository) {
            throw new RuntimeException();
        }

        $this->internalRepository = $repository;
    }

    /**
     * Find entities by an email address.
     *
     * @param string $address An email address.
     * @return Entity[]
     */
    public function find(string $address): array
    {
        $emailAddress = $this->repository->getByAddress($address);

        if (!$emailAddress) {
            return [];
        }

        return $this->internalRepository->getEntityListByAddressId($emailAddress->getId());
    }

    /**
     * Find a first entity by an email address.
     *
     * @param string $address An email address.
     * @param string[] $order An order entity type list.
     */
    public function findOne(string $address, ?array $order = null): ?Entity
    {
        if ($order) {
            $this->internalRepository->getEntityByAddress($address, null, $order);
        }

        return $this->internalRepository->getEntityByAddress($address);
    }
}
