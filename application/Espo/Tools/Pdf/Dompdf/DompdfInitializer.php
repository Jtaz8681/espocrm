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

use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Module;
use Espo\Tools\Pdf\Params;
use Espo\Tools\Pdf\Template;
use RuntimeException;

class DompdfInitializer
{
    private string $defaultFontFace = 'DejaVu Sans';
    private string $cacheDir = 'data/cache/application/dompdf';
    private string $pdfaCacheDir = 'data/cache/application/pdfa-dompdf';

    private const PT = 2.83465;

    /** @var array<string, string> */
    private array $standardFontMapping = [
        'courier' => 'DejaVu Sans Mono',
        'fixed' => 'DejaVu Sans Mono',
        'helvetica' => 'DejaVu Sans',
        'monospace' => 'DejaVu Sans Mono',
        'sans-serif' => 'DejaVu Sans',
        'serif' => 'DejaVu Serif',
        'times' => 'DejaVu Serif',
        'times-roman' => 'DejaVu Serif',
    ];

    public function __construct(
        private Config $config,
        private Metadata $metadata,
        private FileManager $fileManager,
        private Module $module,
    ) {}

    public function initialize(Template $template, Params $params): Dompdf
    {
        $options = new Options();

        $options
            ->setIsPdfAEnabled($params->isPdfA())
            ->setDefaultFont($this->getFontFace($template))
            ->setIsJavascriptEnabled(false);

        $dir = $params->isPdfA() ? $this->pdfaCacheDir : $this->cacheDir;

        $options->setFontDir($dir);
        $options->setFontCache($dir);

        if (!$this->fileManager->isDir($dir)) {
            $this->fileManager->mkdir($dir);
        }

        $this->setupFontOptions($options);

        $pdf = new Dompdf($options);

        $this->mapFonts($pdf, $params->isPdfA(), $dir);

        $size = $template->getPageFormat() === Template::PAGE_FORMAT_CUSTOM ?
            [0.0, 0.0, $template->getPageWidth() * self::PT, $template->getPageHeight() * self::PT] :
            $template->getPageFormat();

        $orientation = $template->getPageOrientation() === Template::PAGE_ORIENTATION_PORTRAIT ?
            'portrait' :
            'landscape';

        $pdf->setPaper($size, $orientation);

        return $pdf;
    }

    private function getFontFace(Template $template): string
    {
        return
            $template->getFontFace() ??
            $this->config->get('pdfFontFace') ??
            $this->defaultFontFace;
    }

    private function mapFonts(Dompdf $pdf, bool $isPdfA, string $dir): void
    {
        $file = $dir . '/' . FontMetrics::USER_FONTS_FILE;

        if ($this->fileManager->exists($file)) {
            return;
        }

        // When fonts are included in PDF/A, we need to map standard fonts to open source analogues.
        // Also need to support popular fonts specified in CSS styles.
        $fontMetrics = $pdf->getFontMetrics();

        if ($isPdfA) {
            foreach ($this->standardFontMapping as $key => $value) {
                $fontMetrics->setFontFamily($key, $fontMetrics->getFamily($value));
            }

            return;
        }

        $this->setupAdditionalFonts($pdf);

        /** @var string[] $fontList */
        $fontList = $this->metadata->get('app.pdfEngines.Dompdf.fontFaceList') ?? [];
        $fontList = array_map(fn ($it) => strtolower($it), $fontList);

        foreach ($this->standardFontMapping as $key => $value) {
            if (in_array(strtolower($key), $fontList)) {
                continue;
            }

            $fontMetrics->setFontFamily($key, $fontMetrics->getFamily($value));
        }
    }

    private function setupFontOptions(Options $options): void
    {
        $dirs = ['application/Espo/Resources/fonts'];

        foreach ($this->module->getOrderedList() as $module) {
            $dirs[] = $this->module->getModulePath($module) . '/Resources/fonts';
        }

        $dirs[] = 'custom/Espo/Custom/Resources/fonts';

        $dirs = array_filter($dirs, fn ($dir) => $this->fileManager->isDir($dir));
        $dirs = array_values($dirs);

        $options->setChroot($dirs);
    }

    private function setupAdditionalFonts(Dompdf $pdf): void
    {
        /** @var array{family?: string, style?: string, weight?: string, source?: string}[] $fonts */
        $fonts = $this->metadata->get("app.pdfEngines.Dompdf.additionalParams.fonts") ?? [];

        foreach ($fonts as $defs) {
            $family = $defs['family'] ?? throw new RuntimeException("No font 'family'.");
            $style = $defs['style'] ?? throw new RuntimeException("No font 'style'.");
            $weight = $defs['weight'] ?? throw new RuntimeException("No font 'weight'.");
            $source = $defs['source'] ?? throw new RuntimeException("No font 'source'.");

            $this->registerFont(
                pdf: $pdf,
                family: $family,
                style: $style,
                weight: $weight,
                source: $source,
            );
        }
    }

    private function registerFont(
        Dompdf $pdf,
        string $family,
        string $style,
        string $weight,
        string $source,
    ): void {

        $fontMetrics = $pdf->getFontMetrics();

        $fontMetrics->registerFont([
            'family' => $family,
            'style' => $style,
            'weight' => $weight,
        ], $source);
    }
}
