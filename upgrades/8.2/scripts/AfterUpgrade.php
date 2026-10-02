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
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Template;
use Espo\ORM\EntityManager;
use Espo\Tools\Pdf\Template as PdfTemplate;

/** @noinspection PhpMultipleClassDeclarationsInspection */
class AfterUpgrade
{
    public function run(Container $container): void
    {
        $configWriter = $container->getByClass(InjectableFactory::class)
            ->create(ConfigWriter::class);

        $configWriter->setMultiple([
            'jobForceUtc' => true,
        ]);

        $configWriter->save();

        $em = $container->getByClass(EntityManager::class);
        $config = $container->getByClass(Config::class);

        $this->updateTemplates($em, $config);
        $this->updateTargetList($container->getByClass(Metadata::class));
    }

    private function updateTemplates(EntityManager $entityManager, Config $config): void
    {
        if ($config->get('pdfEngine') !== 'Dompdf') {
            return;
        }

        /** @var iterable<Template> $templates */
        $templates = $entityManager->getRDBRepositoryByClass(Template::class)
            ->sth()
            ->where(['pageFormat' => PdfTemplate::PAGE_FORMAT_CUSTOM])
            ->find();

        foreach ($templates as $template) {
            $width = $template->get('pageWidth') ?? 0.0;
            $height = $template->get('pageHeight') ?? 0.0;

            $template->setMultiple([
                'pageWidth' => $width / 2.83465,
                'pageHeight' => $height / 2.83465,
            ]);

            $entityManager->saveEntity($template);
        }
    }

    private function updateTargetList(Metadata $metadata): void
    {
        $links = $metadata->get('entityDefs.TargetList.links') ?? [];

        $toSave = false;

        foreach ($links as $link => $defs) {
            if (empty($defs['isCustom'])) {
                continue;
            }

            if (!$metadata->get("clientDefs.TargetList.relationshipPanels.$link.massSelect")) {
                continue;
            }

            $metadata->set('recordDefs', 'TargetList', [
                'relationships' => [
                    $link => [
                        'massLink' => true,
                        'linkRequiredForeignAccess' => 'read',
                        'mandatoryAttributeList' => ['targetListIsOptedOut'],
                    ]
                ]
            ]);

            $toSave = true;
        }

        if (!$toSave) {
            return;
        }

        $metadata->save();
    }
}
