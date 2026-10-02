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

namespace Espo\Tools\Layout;

use Espo\Core\DataManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Language;
use Espo\Core\Utils\Metadata;
use RuntimeException;

class CustomLayoutService
{
    private const TYPE_LIST = 'list';

    public function __construct(
        private Metadata $metadata,
        private FileManager $fileManager,
        private Language $baseLanguage,
        private LayoutProvider $layoutProvider,
        private DataManager $dataManager
    ) {}

    /**
     * @throws Conflict
     * @throws BadRequest
     * @throws Forbidden
     */
    public function create(LayoutDefs $defs): void
    {
        $type = $defs->getType();
        $name = $defs->getName();
        $scope = $defs->getScope();
        $label = $defs->getLabel();

        $this->checkName($name);

        if ($type !== self::TYPE_LIST) {
            throw new BadRequest("Not supported type.");
        }

        if (!$this->metadata->get(['scopes', $scope, 'entity'])) {
            throw new Forbidden("Bad scope.");
        }

        if (
            $this->metadata->get(['clientDefs', $scope, 'additionalLayouts', $name]) ||
            $this->fileManager->exists("application/Espo/Resources/defaults/layouts/$name") ||
            $this->layoutProvider->get($scope, $name)
        ) {
            throw Conflict::createWithBody(
                "Layout $name already exists.",
                Error\Body::create()
                    ->withMessageTranslation('alreadyExists', 'LayoutManager', ['name' => $name])
                    ->encode()
            );
        }

        $this->writeDefaultListLayout($scope, $name);

        $this->metadata->set('clientDefs', $scope, [
            'additionalLayouts' => [
                $name => [
                    'type' => $type,
                    'isCustom' => true,
                ]
            ]
        ]);

        $this->baseLanguage->set($scope, 'layouts', $name, $label);

        $this->metadata->save();
        $this->baseLanguage->save();

        $this->clearCache();
    }

    private function clearCache(): void
    {
        try {
            $this->dataManager->clearCache();
        } catch (Error $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function writeDefaultListLayout(string $scope, string $name): void
    {
        $file = $this->composerFilePath($scope, $name);

        $listLayout = [
            (object) [
                'name' => 'name',
                'link' => true,
            ],
        ];

        $this->fileManager->putJsonContents($file, $listLayout);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function delete(string $scope, string $name): void
    {
        if (!$this->metadata->get(['scopes', $scope, 'entity'])) {
            throw new Forbidden("Bad scope.");
        }

        $this->checkName($name);

        $file = $this->composerFilePath($scope, $name);

        $this->metadata->delete('clientDefs', $scope, "additionalLayouts.$name");
        $this->baseLanguage->delete($scope, 'layouts', $name);

        $this->metadata->save();
        $this->baseLanguage->save();
        $this->fileManager->remove($file);

        $this->clearCache();
    }

    private function composerFilePath(string $scope, string $name): string
    {
        return "custom/Espo/Custom/Resources/layouts/$scope/$name.json";
    }

    /**
     * @throws BadRequest
     */
    private function checkName(string $name): void
    {
        if (
            lcfirst($name[0]) !== $name[0] ||
            preg_match('/[^a-zA-Z\d]/', $name)
        ) {
            throw new BadRequest("Bad name.");
        }
    }
}
