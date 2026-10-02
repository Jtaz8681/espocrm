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

namespace Espo\Tools\Pdf;

use Espo\Core\Exceptions\Error;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Collection;
use Espo\ORM\Entity;

class PrinterController
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private Template $template,
        private string $engine
    ) {}

    /**
     * @throws Error
     */
    public function printEntity(Entity $entity, ?Params $params, ?Data $data = null): Contents
    {
        $params = $params ?? new Params();
        $data = $data ?? new Data();

        return $this->createEntityPrinter()->print($this->template, $entity, $params,  $data);
    }

    /**
     * @param Collection<Entity> $collection
     * @throws Error
     */
    public function printCollection(
        Collection $collection,
        ?Params $params,
        ?IdDataMap $idDataMap = null
    ): Contents {

        $params = $params ?? new Params();
        $idDataMap = $idDataMap ?? new IdDataMap();

        if ($this->hasCollectionPrinter()) {
            return $this->createCollectionPrinter()->print($this->template, $collection, $params, $idDataMap);
        }

        $printer = $this->createEntityPrinter();

        $zipper = new Zipper();

        foreach ($collection as $entity) {
            $data = $idDataMap->get($entity->getId()) ?? new Data();

            $itemContents = $printer->print($this->template, $entity, $params, $data);

            $zipper->add($itemContents, $entity->getId());
        }

        $zipper->archive();

        return new ZipContents($zipper->getFilePath());
    }

    /**
     * @throws Error
     */
    private function createEntityPrinter(): EntityPrinter
    {
        /** @var ?class-string<EntityPrinter> $className */
        $className = $this->metadata
            ->get(['app', 'pdfEngines', $this->engine, 'implementationClassNameMap', 'entity']) ?? null;

        if (!$className) {
            throw new Error("Unknown PDF engine '{$this->engine}', type 'entity'.");
        }

        return $this->injectableFactory->create($className);
    }

    /**
     * @throws Error
     */
    private function createCollectionPrinter(): CollectionPrinter
    {
        $className = $this->getCollectionPrinterClassName();

        if (!$className) {
            throw new Error("Unknown PDF engine '{$this->engine}', type 'collection'.");
        }

        return $this->injectableFactory->create($className);
    }

    private function hasCollectionPrinter(): bool
    {
        return (bool) $this->getCollectionPrinterClassName();
    }

    /**
     * @return ?class-string<CollectionPrinter>
     */
    private function getCollectionPrinterClassName(): ?string
    {
        /** @var ?class-string<CollectionPrinter> */
        return $this->metadata
            ->get(['app', 'pdfEngines', $this->engine, 'implementationClassNameMap', 'collection']) ?? null;
    }

}
