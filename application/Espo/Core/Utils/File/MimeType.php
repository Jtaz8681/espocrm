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

namespace Espo\Core\Utils\File;

use Espo\Core\Utils\Metadata;

class MimeType
{
    public function __construct(private Metadata $metadata)
    {}

    /**
     * @return string[]
     */
    public function getMimeTypeListByExtension(string $extension): array
    {
        $extensionLowerCase = strtolower($extension);

        /** @var string[] */
        return $this->metadata
            ->get(['app', 'file', 'extensionMimeTypeMap', $extensionLowerCase]) ?? [];
    }

    public function getMimeTypeByExtension(string $extension): ?string
    {
        $typeList = $this->getMimeTypeListByExtension($extension);

        return $typeList[0] ?? null;
    }

    public static function matchMimeTypeToAcceptToken(string $mimeType, string $token): bool
    {
        if ($mimeType === $token) {
            return true;
        }

        if (in_array($token, ['audio/*', 'video/*', 'image/*'])) {
            return strpos($mimeType, substr($token, 0, -2)) === 0;
        }

        return false;
    }
}
