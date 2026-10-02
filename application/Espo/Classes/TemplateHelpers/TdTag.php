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

class TdTag implements Helper
{
    public function render(Data $data): Result
    {
        $align = strtolower($data->getOption('align') ?? 'left');

        if (!in_array($align, ['left', 'right', 'center'])) {
            $align = 'left';
        }

        $width = $data->getOption('width') ?? null;

        $attributesPart = "align=\"{$align}\"";

        if ($width) {
            $attributesPart .= " width=\"{$width}\"";
        }

        $function = $data->getFunction();

        $content = $function !== null ? $function() : '';

        return Result::createSafeString(
            "<td {$attributesPart}>{$content}</td>"
        );
    }
}
