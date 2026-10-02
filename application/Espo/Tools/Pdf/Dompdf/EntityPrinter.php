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

namespace Espo\Tools\Pdf\Dompdf;

use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Espo\Core\FileStorage\Manager;
use Espo\ORM\Entity;
use Espo\Tools\Pdf\Contents;
use Espo\Tools\Pdf\Data;
use Espo\Tools\Pdf\Dompdf\Contents as DompdfContents;
use Espo\Tools\Pdf\EntityPrinter as EntityPrinterInterface;
use Espo\Tools\Pdf\Params;
use Espo\Tools\Pdf\Template;
use RuntimeException;

class EntityPrinter implements EntityPrinterInterface
{
    public function __construct(
        private DompdfInitializer $dompdfInitializer,
        private HtmlComposer $htmlComposer,
        private Manager $fileStorageManager,
    ) {}

    public function print(Template $template, Entity $entity, Params $params, Data $data): Contents
    {
        $pdf = $this->dompdfInitializer->initialize($template, $params);

        $headHtml = $this->htmlComposer->composeHead($template, $entity);
        $headerFooterHtml = $this->htmlComposer->composeHeaderFooter($template, $entity, $params, $data);
        $mainHtml = $this->htmlComposer->composeMain($template, $entity, $params, $data);

        $html = $headHtml . "\n<body>" . $headerFooterHtml . $mainHtml . "</body>";

        $pdf->loadHtml($html);
        $pdf->render();

        $this->addAttachments($pdf, $data);

        return new DompdfContents($pdf);
    }

    private function addAttachments(Dompdf $pdf, Data $data): void
    {
        if ($data->getAttachments() === []) {
            return;
        }

        $canvas = $pdf->getCanvas();

        if (!$canvas instanceof CPDF) {
            throw new RuntimeException("Non CPDF canvas");
        }

        $cPdf = $canvas->get_cpdf();

        foreach ($data->getAttachments() as $i => $attachmentWrapper) {
            $attachment = $attachmentWrapper->getAttachment();

            $path = $this->fileStorageManager->getLocalFilePath($attachment);

            $name = $attachment->getName() ?? 'file-' . $i;
            $description = $attachmentWrapper->getDescription() ?? '';

            $cPdf->addEmbeddedFile($path, $name, $description);
        }
    }
}
