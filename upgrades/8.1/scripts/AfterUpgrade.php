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

use Espo\Core\Container;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Config\ConfigWriter;

class AfterUpgrade
{
    public function run(Container $container): void
    {
        $config = $container->getByClass(Espo\Core\Utils\Config::class);

        $configWriter = $container->getByClass(InjectableFactory::class)
            ->create(ConfigWriter::class);

        $configWriter->setMultiple([
            'phoneNumberNumericSearch' => false,
            'phoneNumberInternational' => false,
        ]);

        if ($config->get('pdfEngine') === 'Tcpdf') {
            $configWriter->set('pdfEngine', 'Dompdf');
        }

        $configWriter->save();
    }
}
