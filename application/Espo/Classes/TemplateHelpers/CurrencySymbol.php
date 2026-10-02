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

namespace Espo\Classes\TemplateHelpers;

use Espo\Core\Htmlizer\Helper;
use Espo\Core\Htmlizer\Helper\Data;
use Espo\Core\Htmlizer\Helper\Result;
use Espo\Core\Utils\Metadata;

/**
 * @noinspection PhpUnused
 */
class CurrencySymbol implements Helper
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function render(Data $data): Result
    {
        $code = $data->getArgumentList()[0] ?? null;

        if (!$code || !is_string($code)) {
            return Result::createEmpty();
        }

        $symbol = $this->metadata->get("app.currency.symbolMap.$code");

        return Result::create($symbol);
    }
}
