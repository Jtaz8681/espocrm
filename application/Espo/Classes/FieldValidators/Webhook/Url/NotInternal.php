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

namespace Espo\Classes\FieldValidators\Webhook\Url;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Core\Utils\Security\UrlCheck;
use Espo\Core\Webhook\AddressUtil;
use Espo\ORM\Entity;

/**
 * @implements Validator<Entity>
 */
class NotInternal implements Validator
{
    public function __construct(
        private UrlCheck $urlCheck,
        private AddressUtil $addressUtil,
    ) {}

    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $value = $entity->get($field);

        if (!$value) {
            return null;
        }

        if (!$this->urlCheck->isUrl($value)) {
            return null;
        }

        if ($this->addressUtil->isAllowedUrl($value)) {
            return null;
        }

        if (!$this->urlCheck->isUrlAndNotInternal($value)) {
            return Failure::create();
        }

        return null;
    }
}
