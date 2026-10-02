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

namespace Espo\Core\Acl\Map;

use Espo\Core\Utils\Metadata;

class MetadataProvider
{
    protected string $type = 'acl';

    public function __construct(private Metadata $metadata)
    {}

    /**
     * @return string[]
     */
    public function getScopeList(): array
    {
        /** @var string[] */
        return array_keys($this->metadata->get('scopes') ?? []);
    }

    public function isScopeEntity(string $scope): bool
    {
        return (bool) $this->metadata->get(['scopes', $scope, 'entity']);
    }

    /**
     * @return string[]
     */
    public function getScopeFieldList(string $scope): array
    {
        /** @var string[] */
        return array_keys($this->metadata->get(['entityDefs', $scope, 'fields']) ?? []);
    }

    /**
     * @return array<int, string>
     */
    public function getPermissionList(): array
    {
        $itemList = $this->metadata->get(['app', $this->type, 'valuePermissionList']) ?? [];

        return array_map(
            function (string $item): string {
                if (str_ends_with($item, 'Permission')) {
                    return substr($item, 0, -10);
                }

                return $item;
            },
            $itemList
        );
    }
}
