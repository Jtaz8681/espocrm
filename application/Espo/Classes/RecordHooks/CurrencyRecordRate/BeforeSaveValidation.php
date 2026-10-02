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

namespace Espo\Classes\RecordHooks\CurrencyRecordRate;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\CurrencyRecordRate;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<CurrencyRecordRate>
 */
class BeforeSaveValidation implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function process(Entity $entity): void
    {
        $this->validateDate($entity);
    }

    /**
     * @throws Conflict
     */
    private function validateDate(CurrencyRecordRate $entity): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $recordId = $entity->getRecord()->getId();
        $date = $entity->getDate();

        $one = $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecordRate::class)
            ->where([
                CurrencyRecordRate::ATTR_RECORD_ID => $recordId,
                CurrencyRecordRate::FIELD_DATE => $date->toString(),
            ])
            ->findOne();

        if ($one) {
            throw Conflict::createWithBody(
                'rateOnDateAlreadyExists',
                Body::create()->withMessageTranslation('rateOnDateAlreadyExists', 'Currency')
            );
        }
    }
}
