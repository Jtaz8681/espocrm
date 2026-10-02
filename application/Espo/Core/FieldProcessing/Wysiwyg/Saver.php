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

namespace Espo\Core\FieldProcessing\Wysiwyg;

use Espo\Core\ORM\Type\FieldType;
use Espo\Entities\Attachment;
use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\EntityManager;

/**
 * @implements SaverInterface<Entity>
 */
class Saver implements SaverInterface
{
    /** @var array<string, string[]> */
    private $fieldListMapCache = [];

    public function __construct(private EntityManager $entityManager)
    {}

    public function process(Entity $entity, Params $params): void
    {
        foreach ($this->getFieldList($entity->getEntityType()) as $name) {
            $this->processItem($entity, $name);
        }
    }

    private function processItem(Entity $entity, string $name): void
    {
        if (!$entity->has($name)) {
            return;
        }

        if (!$entity->isAttributeChanged($name)) {
            return;
        }

        $contents = $entity->get($name);

        if (!$contents) {
            return;
        }

        $matches = [];

        $matchResult = preg_match_all("/\?entryPoint=attachment&amp;id=([^&=\"']+)/", $contents, $matches);

        if (!$matchResult) {
            return;
        }

        if (empty($matches[1]) || !is_array($matches[1])) {
            return;
        }

        foreach ($matches[1] as $id) {
            $attachment = $this->entityManager->getRDBRepositoryByClass(Attachment::class)->getById($id);

            if (!$attachment) {
                continue;
            }

            if ($attachment->getRelated()) {
                continue;
            }

            $attachment->set([
                'relatedId' => $entity->getId(),
                'relatedType' => $entity->getEntityType(),
            ]);

            $this->entityManager->saveEntity($attachment);
        }
    }

    /**
     * @return string[]
     */
    private function getFieldList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->fieldListMapCache)) {
            return $this->fieldListMapCache[$entityType];
        }

        $entityDefs = $this->entityManager
            ->getDefs()
            ->getEntity($entityType);

        $list = [];

        foreach ($entityDefs->getFieldNameList() as $name) {
            $defs = $entityDefs->getField($name);

            if ($defs->getType() !== FieldType::WYSIWYG) {
                continue;
            }

            $list[] = $name;
        }

        $this->fieldListMapCache[$entityType] = $list;

        return $list;
    }
}
