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

namespace Espo\Core\Utils\Client;

use Espo\Core\Utils\File\Manager as FileManager;
use RuntimeException;

/**
 * @internal Also used by the installer w/o DI.
 */
class DevModeJsFileListProvider
{
    private const LIBS_FILE = 'frontend/libs.json';

    public function __construct(private FileManager $fileManager)
    {}

    /**
     * @return string[]
     */
    public function get(): array
    {
        $list = [];

        $items = json_decode($this->fileManager->getContents(self::LIBS_FILE));

        foreach ($items as $item) {
            if (!($item->bundle ?? false)) {
                continue;
            }

            $files = $item->files ?? null;

            if ($files !== null) {
                $list = array_merge(
                    $list,
                    array_map(
                        fn ($item) => self::prepareBundleLibFilePath($item),
                        $files
                    )
                );

                continue;
            }

            if (!isset($item->src)) {
                continue;
            }

            $list[] = self::prepareBundleLibFilePath($item);
        }

        return $list;
    }


    private function prepareBundleLibFilePath(object $item): string
    {
        $amdId = $item->amdId ?? null;

        if ($amdId) {
            $file = $amdId;

            if (str_starts_with($amdId, '@')) {
                $file = substr($amdId, 1);
                $file = str_replace('/', '-', $file);
            }

            return 'client/lib/original/' . $file . '.js';
        }

        $src = $item->src ?? null;

        if (!$src) {
            throw new RuntimeException("Missing 'src' in bundled lib definition.");
        }

        $arr = explode('/', $src);

        return 'client/lib/original/' . array_slice($arr, -1)[0];
    }
}
