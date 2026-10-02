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

class PostData
{
    protected $data = [];

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        if (isset($_POST) && is_array($_POST)) {
            $this->data = $_POST;
        }
    }

    public function set($name, $value = null)
    {
        if (!is_array($name)) {
            $name = [
                $name => $value
            ];
        }

        foreach ($name as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    public function getAll()
    {
        return $this->data;
    }
}
