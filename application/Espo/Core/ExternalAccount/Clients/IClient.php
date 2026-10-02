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

namespace Espo\Core\ExternalAccount\Clients;

interface IClient
{
    /**
     * @param string $name
     * @return mixed
     */
    public function getParam($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setParam($name, $value);

    /**
     * @param array<string, mixed> $params
     * @return mixed
     */
    public function setParams(array $params);

    /**
     * @return bool
     */
    public function ping();
}
