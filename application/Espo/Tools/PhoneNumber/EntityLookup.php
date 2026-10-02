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

namespace Espo\Tools\PhoneNumber;

use Espo\Entities\PhoneNumber;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Repositories\PhoneNumber as PhoneNumberRepository;

use RuntimeException;

/**
 * Entity lookup by a phone number.
 */
class EntityLookup
{
    private PhoneNumberRepository $internalRepository;

    public function __construct(
        private Repository $repository,
        EntityManager $entityManager
    ) {
        $repository = $entityManager->getRDBRepository(PhoneNumber::ENTITY_TYPE);

        if (!$repository instanceof PhoneNumberRepository) {
            throw new RuntimeException();
        }

        $this->internalRepository = $repository;
    }

    /**
     * Find entities by a phone number.
     *
     * @param string $number A phone number.
     * @return Entity[]
     */
    public function find(string $number): array
    {
        $phoneNumber = $this->repository->getByNumber($number);

        if (!$phoneNumber) {
            return [];
        }

        return $this->internalRepository->getEntityListByPhoneNumberId($phoneNumber->getId());
    }

    /**
     * Find a first entity by a phone number.
     *
     * @param string $number A phone number.
     * @param string[] $order An order entity type list.
     */
    public function findOne(string $number, ?array $order = null): ?Entity
    {
        $phoneNumber = $this->repository->getByNumber($number);

        if (!$phoneNumber) {
            return null;
        }

        if ($order) {
            $this->internalRepository->getEntityByPhoneNumberId($phoneNumber->getId(), null, $order);
        }

        return $this->internalRepository->getEntityByPhoneNumberId($phoneNumber->getId());
    }
}
