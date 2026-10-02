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

namespace Espo\Core\Mail\Sender;

use Espo\Core\InjectableFactory;
use Espo\Core\Mail\SmtpParams;

class TransportPreparatorFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
    ) {}

    public function create(SmtpParams $smtpParams): TransportPreparator
    {
        $className = $smtpParams->getTransportPreparatorClassName() ?? DefaultTransportPreparator::class;

        return $this->injectableFactory->create($className);
    }
}
