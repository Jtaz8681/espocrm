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

namespace Espo\Modules\Crm;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingProcessor;

class Binding implements BindingProcessor
{
    public function process(Binder $binder): void
    {
        $binder->bindImplementation(
            'Espo\\Modules\\Crm\\Tools\\MassEmail\\MessageHeadersPreparator',
            'Espo\\Modules\\Crm\\Tools\\MassEmail\\DefaultMessageHeadersPreparator'
        );

        $binder->bindImplementation(
            'Espo\\Modules\\Crm\\Tools\\EmailAddress\\FreeDomainChecker',
            'Espo\\Modules\\Crm\\Tools\\EmailAddress\\DefaultFreeDomainChecker'
        );
    }
}
