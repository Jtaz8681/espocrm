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

namespace Espo\Tools\EntityManager\Hook\Hooks;

use Espo\Core\DataManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\InjectableFactory;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Templates\Entities\Base;
use Espo\Core\Templates\Entities\BasePlus;
use Espo\Core\Templates\Entities\CategoryTree;
use Espo\Core\Utils\Language;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Type\RelationType;
use Espo\Tools\EntityManager\CreateParams;
use Espo\Tools\EntityManager\DeleteParams;
use Espo\Tools\EntityManager\EntityManager;
use Espo\Tools\EntityManager\Hook\UpdateHook;
use Espo\Tools\EntityManager\Params;
use Espo\Tools\LayoutManager\LayoutCustomizer;
use Espo\Tools\LayoutManager\LayoutName;

/**
 * @noinspection PhpUnused
 */
class CategoriesUpdateHook implements UpdateHook
{
    private const string PARAM = 'categories';
    private const string FIELD = 'category';

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Language $defaultLanguage,
        private Metadata $metadata,
        private DataManager $dataManager,
        private LayoutCustomizer $layoutCustomizer,
        private Log $log,
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws Conflict
     */
    public function process(Params $params, Params $previousParams): void
    {
        if (!in_array($params->getType(), [BasePlus::TEMPLATE_TYPE, Base::TEMPLATE_TYPE])) {
            return;
        }

        if ($params->get(self::PARAM) && !$previousParams->get(self::PARAM)) {
            $this->add($params->getName());
        } else if (!$params->get(self::PARAM) && $previousParams->get(self::PARAM)) {
            $this->remove($params->getName());
        }
    }

    /**
     * @throws BadRequest
     * @throws Conflict
     * @throws Error
     */
    private function add(string $name): void
    {
        $entityType = $this->composeEntityType($name);

        if ($this->metadata->get("scopes.$entityType")) {
            $message = "Could not create category entity type $entityType as the same entity type exists";

            $this->log->warning($message);

            return;
        }

        $createParams = new CreateParams(
            forceCreate: true,
            replaceData: [
                'subjectEntityType' => $name,
            ],
            skipCustomPrefix: true,
            isNotRemovable: true,
            addTab: false,
        );

        $this->getEntityManagerTool()->create(
            name: $entityType,
            type: CategoryTree::TEMPLATE_TYPE,
            params: [
                'labelSingular' => $this->defaultLanguage->translateLabel($name, 'scopeNames') . ' ' .
                    $this->defaultLanguage->translateLabel('Category', 'entityNameParts', 'EntityManager'),
                'labelPlural' => $this->defaultLanguage->translateLabel($name, 'scopeNames') . ' ' .
                    $this->defaultLanguage->translateLabel('Category', 'entityNamePartsPlural', 'EntityManager')
            ],
            createParams: $createParams,
        );

        $this->metadata->set('entityDefs', $name, [
            'fields' => [
                self::FIELD => [
                    FieldParam::TYPE => FieldType::LINK,
                    'audited' => true,
                    'view' => 'views/fields/link-category-tree'
                ]
            ],
            'links' => [
                self::FIELD => [
                    RelationParam::TYPE => RelationType::BELONGS_TO,
                    RelationParam::ENTITY => $entityType,
                ]
            ],
        ]);

        $this->metadata->set('clientDefs', $name, [
            'views' => [
                'list' => 'views/list-with-categories',
            ],
            'modalViews' => [
                'select' => 'views/modals/select-records-with-categories',
            ],
        ]);

        $this->metadata->save();
        $this->dataManager->rebuild([$entityType]);
        $this->dataManager->rebuild([$name]);

        $this->layoutCustomizer->addDetailField($name, self::FIELD, LayoutName::DETAIL);
        $this->layoutCustomizer->addDetailField($name, self::FIELD, LayoutName::DETAIL_SMALL);
    }

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function remove(string $name): void
    {
        $entityType = $this->composeEntityType($name);

        $deleteParams = new DeleteParams(
            forceRemove: true,
        );

        $this->getEntityManagerTool()->delete($entityType, $deleteParams);

        $this->metadata->delete('entityDefs', $name, [
            'fields.' . self::FIELD,
            'links.' . self::FIELD,
        ]);

        $this->metadata->delete('clientDefs', $name, [
            'views.list',
            'modalViews.select',
        ]);

        $this->metadata->save();
        $this->dataManager->rebuild([$entityType]);
        $this->dataManager->rebuild([$name]);

        $this->layoutCustomizer->removeInDetail($name, self::FIELD, LayoutName::DETAIL);
        $this->layoutCustomizer->removeInDetail($name, self::FIELD, LayoutName::DETAIL_SMALL);
    }

    private function getEntityManagerTool(): EntityManager
    {
        return $this->injectableFactory->create(EntityManager::class);
    }

    private function composeEntityType(string $name): string
    {
        return $name . 'Category';
    }
}
