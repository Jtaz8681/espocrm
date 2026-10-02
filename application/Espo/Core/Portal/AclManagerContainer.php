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

namespace Espo\Core\Portal;

use Espo\Entities\Portal;
use Espo\Core\InjectableFactory;

use LogicException;

/**
 * Used when logged to CRM (not to portal) to provide an access checking ability for a specific portal.
 * E.g. check whether a portal user has access to some record within a specific portal.
 */
class AclManagerContainer
{
    /**
     * @var array<string, AclManager>
     */
    private $data = [];

    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function get(Portal $portal): AclManager
    {
        if (!$portal->hasId()) {
            throw new LogicException("AclManagerContainer: portal should have ID.");
        }

        $id = $portal->getId();

        if (!isset($this->data[$id])) {
            $aclManager = $this->injectableFactory->create(AclManager::class);
            $aclManager->setPortal($portal);

            $this->data[$id] = $aclManager;
        }

        return $this->data[$id];
    }
}
