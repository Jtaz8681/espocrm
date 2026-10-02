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

namespace Espo\Tools\LayoutManager;

use Espo\Core\Exceptions\Error;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Json;
use Espo\Tools\Layout\LayoutProvider;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

class LayoutManager
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected $changedData = [];

    public function __construct(
        protected FileManager $fileManager,
        protected LayoutProvider $layoutProvider
    ) {}

    /**
     * Get layout in string format.
     */
    public function get(string $scope, string $name): ?string
    {
        return $this->layoutProvider->get($scope, $name);
    }

    /**
     * Set layout data.
     *
     * @param mixed $data
     * @throws Error
     */
    public function set($data, string $scope, string $name): void
    {
        $scope = $this->sanitizeInput($scope);
        $name = $this->sanitizeInput($name);

        if (empty($scope) || empty($name)) {
            throw new Error("Error while setting layout.");
        }

        $this->changedData[$scope][$name] = $data;
    }

    public function resetToDefault(string $scope, string $name): ?string
    {
        $scope = $this->sanitizeInput($scope);
        $name = $this->sanitizeInput($name);

        $filePath = 'custom/Espo/Custom/Resources/layouts/' . $scope . '/' . $name . '.json';

        if ($this->fileManager->isFile($filePath)) {
            $this->fileManager->removeFile($filePath);
        }

        if (!empty($this->changedData[$scope]) && !empty($this->changedData[$scope][$name])) {
            unset($this->changedData[$scope][$name]);
        }

        return $this->get($scope, $name);
    }

    /**
     * Save changes.
     *
     * @throws Error
     */
    public function save(): void
    {
        $result = true;

        if (empty($this->changedData)) {
            return;
        }

        foreach ($this->changedData as $scope => $rowData) {
            $dirPath = 'custom/Espo/Custom/Resources/layouts/' . $scope . '/';

            foreach ($rowData as $layoutName => $layoutData) {
                if (empty($scope) || empty($layoutName)) {
                    continue;
                }

                $data = Json::encode($layoutData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                $path = $dirPath . $layoutName . '.json';

                $result &= $this->fileManager->putContents($path, $data);
            }
        }

        if (!$result) {
            throw new Error("Error while saving layout.");
        }

        $this->clearChanges();
    }

    /**
     * Clear unsaved changes.
     */
    public function clearChanges(): void
    {
        $this->changedData = [];
    }

    protected function sanitizeInput(string $name): string
    {
        /** @var string */
        return preg_replace("([.]{2,})", '', $name);
    }
}
