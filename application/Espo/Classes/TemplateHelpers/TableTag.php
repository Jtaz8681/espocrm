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

class TableTag implements Helper
{
    public function render(Data $data): Result
    {
        $border = $data->getOption('border') ?? '0.5pt';
        $cellpadding = $data->getOption('cellpadding') ?? '2';
        $width = $data->getOption('width') ?? null;

        $attributesPart = "";

        if ($width) {
            $attributesPart .= " width=\"{$width}\"";
        }

        $function = $data->getFunction();

        $content = $function !== null ? $function() : '';

        $style = "border: {$border}; border-spacing: 0; border-collapse: collapse;";

        return Result::createSafeString(
            "<table style=\"{$style}\" border=\"{$border}\" cellpadding=\"{$cellpadding}\" {$attributesPart}>" .
            $content .
            "</table>"
        );
    }
}
