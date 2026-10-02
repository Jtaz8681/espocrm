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

namespace Espo\Classes\AppParams;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Entities\Template;
use Espo\Tools\App\AppParam;
use RuntimeException;

/**
 * Returns a list of entity types for which a PDF template exists.
 *
 * @noinspection PhpUnused
 */
class TemplateEntityTypeList implements AppParam
{

    public function __construct(
        private Acl $acl,
        private SelectBuilderFactory $selectBuilderFactory,
        private EntityManager $entityManager,
    ) {}

    /**
     * @return string[]
     */
    public function get(): array
    {
        if (!$this->acl->checkScope(Template::ENTITY_TYPE)) {
            return [];
        }

        $list = [];

        try {
            $query = $this->selectBuilderFactory
                ->create()
                ->from(Template::ENTITY_TYPE)
                ->withAccessControlFilter()
                ->buildQueryBuilder()
                ->select(['entityType'])
                ->where(['status' => Template::STATUS_ACTIVE])
                ->group(['entityType'])
                ->build();
        } catch (BadRequest|Forbidden $e) {
            throw new RuntimeException('', 0, $e);
        }

        $templateCollection = $this->entityManager
            ->getRDBRepositoryByClass(Template::class)
            ->clone($query)
            ->find();

        foreach ($templateCollection as $template) {
            $list[] = $template->getTargetEntityType();
        }

        return $list;
    }
}
