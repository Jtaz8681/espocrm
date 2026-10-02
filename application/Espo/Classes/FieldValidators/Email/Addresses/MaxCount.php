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

namespace Espo\Classes\FieldValidators\Email\Addresses;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Core\Utils\Config;
use Espo\Entities\Email;
use Espo\ORM\Entity;

use LogicException;

/**
 * @implements Validator<Email>
 */
class MaxCount implements Validator
{
    private const MAX_COUNT = 100;

    public function __construct(private Config $config) {}

    /**
     * @param Email $entity
     */
    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        if ($field === 'to') {
            $addresses = $entity->getToAddressList();
        } else if ($field === 'cc') {
            $addresses = $entity->getCcAddressList();
        } else if ($field === 'bcc') {
            $addresses = $entity->getBccAddressList();
        } else {
            throw new LogicException();
        }

        $maxCount = $this->config->get('emailRecipientAddressMaxCount') ?? self::MAX_COUNT;

        if (count($addresses) > $maxCount) {
            return Failure::create();
        }

        return null;
    }
}
