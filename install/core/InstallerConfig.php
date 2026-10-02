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

class InstallerConfig
{
    private $data;

    private $fileManager;

    protected $configPath = 'install/config.php'; //full path: install/config.php

    public function __construct()
    {
        $this->fileManager = new \Espo\Core\Utils\File\Manager();
    }

    protected function getFileManager()
    {
        return $this->fileManager;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function loadData()
    {
        if (file_exists($this->configPath)) {
            $data = include($this->configPath);
            if (is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    public function getAllData()
    {
        if (!$this->data) {
            $this->data = $this->loadData();
        }

        return $this->data;
    }

    public function get($name, $default = [])
    {
        if (!$this->data) {
            $this->data = $this->loadData();
        }

        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    public function set($name, $value = null)
    {
        if (!is_array($name)) {
            $name = array($name => $value);
        }

        foreach ($name as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function save()
    {
        $data = $this->loadData();

        if (is_array($this->data)) {
            foreach ($this->data as $key => $value) {
                $data[$key] = $value;
            }
        }

        try {
            $result = $this->getFileManager()->putPhpContents($this->configPath, $data);
        } catch (\Exception $e) {
            $GLOBALS['log']->warning($e->getMessage());
            $result = false;
        }


        if ($result) {
            $this->data = null;
        }

        return $result;
    }
}
