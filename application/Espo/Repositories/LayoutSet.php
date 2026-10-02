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

namespace Espo\Repositories;

use Espo\Core\Repositories\Database;
use Espo\Entities\LayoutRecord;
use Espo\Entities\LayoutSet as LayoutSetEntity;
use Espo\ORM\Entity;

/**
 * @extends Database<LayoutSetEntity>
 */
class LayoutSet extends Database
{
    protected function afterSave(Entity $entity, array $options = [])
    {
        parent::afterSave($entity);

        if (!$entity->isNew() && $entity->has('layoutList')) {
            $listBefore = $entity->getFetched('layoutList') ?? [];
            $listNow = $entity->get('layoutList') ?? [];

            foreach ($listBefore as $name) {
                if (!in_array($name, $listNow)) {
                    $layout = $this->entityManager
                        ->getRDBRepository(LayoutRecord::ENTITY_TYPE)
                        ->where([
                            'layoutSetId' => $entity->getId(),
                            'name' => $name,
                        ])
                        ->findOne();

                    if ($layout) {
                        $this->entityManager->removeEntity($layout);
                    }
                }
            }
        }
    }

    protected function afterRemove(Entity $entity, array $options = [])
    {
        parent::afterRemove($entity);

        $layoutList = $this->entityManager
            ->getRDBRepository(LayoutRecord::ENTITY_TYPE)
            ->where([
                'layoutSetId' => $entity->getId(),
            ])
            ->find();

        foreach ($layoutList as $layout) {
            $this->entityManager->removeEntity($layout);
        }
    }
}
