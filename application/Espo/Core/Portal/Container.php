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

use Espo\Core\Container\Exceptions\NotSettableException;
use Espo\Entities\Portal as PortalEntity;
use Espo\Core\Portal\Utils\Config;
use Espo\Core\Container as BaseContainer;

use Psr\Container\NotFoundExceptionInterface;

use LogicException;

class Container extends BaseContainer
{
    private const ID_PORTAL = 'portal';
    private const ID_CONFIG = 'config';
    private const ID_ACL_MANAGER = 'aclManager';

    private bool $portalIsSet = false;

    /**
     * @throws NotSettableException
     */
    public function setPortal(PortalEntity $portal): void
    {
        if ($this->portalIsSet) {
            throw new NotSettableException("Can't set portal second time.");
        }

        $this->portalIsSet = true;

        $this->setForced(self::ID_PORTAL, $portal);

        $data = [];

        foreach ($portal->getSettingsAttributeList() as $attribute) {
            $data[$attribute] = $portal->get($attribute);
        }

        try {
            /** @var Config $config */
            $config = $this->get(self::ID_CONFIG);
            $config->setPortalParameters($data);

            /** @var AclManager $aclManager */
            $aclManager = $this->get(self::ID_ACL_MANAGER);
        } catch (NotFoundExceptionInterface) {
            throw new LogicException();
        }

        $aclManager->setPortal($portal);
    }
}
