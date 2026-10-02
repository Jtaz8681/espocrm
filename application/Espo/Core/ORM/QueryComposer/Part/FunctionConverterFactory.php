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

namespace Espo\Core\ORM\QueryComposer\Part;

use Espo\ORM\QueryComposer\Part\FunctionConverterFactory as FunctionConverterFactoryInterface;
use Espo\ORM\QueryComposer\Part\FunctionConverter;
use Espo\ORM\DatabaseParams;
use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;

use LogicException;

class FunctionConverterFactory implements FunctionConverterFactoryInterface
{
    /** @var array<string, FunctionConverter> */
    private $hash = [];

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private DatabaseParams $databaseParams
    ) {}

    public function create(string $name): FunctionConverter
    {
        $className = $this->getClassName($name);

        if ($className === null) {
            throw new LogicException();
        }

        return $this->injectableFactory->create($className);
    }

    public function isCreatable(string $name): bool
    {
        if ($this->getClassName($name) === null) {
            return false;
        }

        return true;
    }

    /**
     * @return ?class-string<FunctionConverter>
     */
    private function getClassName(string $name): ?string
    {
        if (!array_key_exists($name, $this->hash)) {
            /** @var string $platform */
            $platform = $this->databaseParams->getPlatform();

            $this->hash[$name] =
                $this->metadata->get(['app', 'orm', 'platforms', $platform, 'functionConverterClassNameMap', $name]) ??
                $this->metadata->get(['app', 'orm', 'functionConverterClassNameMap_' . $platform, $name]);

        }

        /** @var ?class-string<FunctionConverter> */
        return $this->hash[$name];
    }
}
