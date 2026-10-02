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

namespace Espo\Core\Utils\Metadata;

use Espo\Core\Utils\Resource\Reader as ResourceReader;
use Espo\Core\Utils\Resource\Reader\Params as ResourceReaderParams;
use Espo\Core\Utils\Util;
use stdClass;

class Builder
{
    /** @var array<int, string[]> */
    private $forceAppendPathList = [
        ['app', 'metadata', 'additionalBuilderClassNameList'],
        ['app', 'rebuild', 'actionClassNameList'],
        ['app', 'formula', 'functionList'],
        ['app', 'fieldProcessing', 'readLoaderClassNameList'],
        ['app', 'fieldProcessing', 'listLoaderClassNameList'],
        ['app', 'fieldProcessing', 'saverClassNameList'],
        ['app', 'hook', 'suppressClassNameList'],
        ['app', 'api', 'globalMiddlewareClassNameList'],
        ['app', 'api', 'routeMiddlewareClassNameListMap', self::ANY_KEY],
        ['app', 'api', 'controllerMiddlewareClassNameListMap', self::ANY_KEY],
        ['app', 'api', 'controllerActionMiddlewareClassNameListMap', self::ANY_KEY],
        ['app', 'entityManager', 'createHookClassNameList'],
        ['app', 'entityManager', 'deleteHookClassNameList'],
        ['app', 'entityManager', 'beforeUpdateHookClassNameList'],
        ['app', 'entityManager', 'updateHookClassNameList'],
        ['app', 'linkManager', 'createHookClassNameList'],
        ['app', 'linkManager', 'deleteHookClassNameList'],
        ['app', 'client', 'scriptList'],
        ['app', 'client', 'cssList'],
        ['app', 'client', 'linkList'],

        ['app', 'record', 'selectApplierClassNameList'],
        ['app', 'record', 'createInputFilterClassNameList'],
        ['app', 'record', 'updateInputFilterClassNameList'],
        ['app', 'record', 'outputFilterClassNameList'],
        ['app', 'record', 'beforeReadHookClassNameList'],
        ['app', 'record', 'earlyBeforeCreateHookClassNameList'],
        ['app', 'record', 'beforeCreateHookClassNameList'],
        ['app', 'record', 'earlyBeforeUpdateHookClassNameList'],
        ['app', 'record', 'beforeUpdateHookClassNameList'],
        ['app', 'record', 'beforeDeleteHookClassNameList'],
        ['app', 'record', 'afterCreateHookClassNameList'],
        ['app', 'record', 'afterUpdateHookClassNameList'],
        ['app', 'record', 'afterDeleteHookClassNameList'],
        ['app', 'record', 'beforeLinkHookClassNameList'],
        ['app', 'record', 'beforeUnlinkHookClassNameList'],
        ['app', 'record', 'afterLinkHookClassNameList'],
        ['app', 'record', 'afterUnlinkHookClassNameList'],

        ['recordDefs', self::ANY_KEY, 'readLoaderClassNameList'],
        ['recordDefs', self::ANY_KEY, 'listLoaderClassNameList'],
        ['recordDefs', self::ANY_KEY, 'saverClassNameList'],
        ['recordDefs', self::ANY_KEY, 'selectApplierClassNameList'],
        ['recordDefs', self::ANY_KEY, 'createInputFilterClassNameList'],
        ['recordDefs', self::ANY_KEY, 'updateInputFilterClassNameList'],
        ['recordDefs', self::ANY_KEY, 'outputFilterClassNameList'],
        ['recordDefs', self::ANY_KEY, 'beforeReadHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'earlyBeforeCreateHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'beforeCreateHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'earlyBeforeUpdateHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'beforeUpdateHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'beforeDeleteHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'afterCreateHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'afterUpdateHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'afterDeleteHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'beforeLinkHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'beforeUnlinkHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'afterLinkHookClassNameList'],
        ['recordDefs', self::ANY_KEY, 'afterUnlinkHookClassNameList'],

        ['clientDefs', self::ANY_KEY, 'detailActionList'],
        ['clientDefs', self::ANY_KEY, 'editActionList'],
        ['clientDefs', self::ANY_KEY, 'modalDetailActionList'],
        ['clientDefs', self::ANY_KEY, 'modalEditActionList'],
    ];

    private const ANY_KEY = '__ANY__';

    public function __construct(private ResourceReader $resourceReader) {}

    public function build(): stdClass
    {
        $readerParams = ResourceReaderParams::create()
            ->withForceAppendPathList($this->forceAppendPathList);

        $data = $this->resourceReader->read('metadata', $readerParams);

        $this->applyAdditional($data);

        return $data;
    }

    private function applyAdditional(stdClass $data): void
    {
        /** @var class-string<AdditionalBuilder>[] $builderClassNameList */
        $builderClassNameList = Util::getValueByKey($data, 'app.metadata.additionalBuilderClassNameList') ?? [];

        /** @var AdditionalBuilder[] $builderList */
        $builderList = array_map(
            fn ($className) => new $className(),
            $builderClassNameList
        );

        foreach ($builderList as $builder) {
            $builder->build($data);
        }
    }
}
