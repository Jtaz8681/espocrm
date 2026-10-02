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

namespace Espo\Classes\Record\Attachment;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Input\Data;
use Espo\Core\Record\Input\Filter;
use Espo\Entities\Attachment;
use Espo\ORM\EntityManager;
use Espo\Tools\Attachment\AccessChecker;
use Espo\Tools\Attachment\DetailsObtainer;
use Espo\Tools\Attachment\FieldData;

/**
 * @noinspection PhpUnused
 */
class CreateInputFilter implements Filter
{
    public function __construct(
        private EntityManager $entityManager,
        private AccessChecker $accessChecker,
        private DetailsObtainer $detailsObtainer
    ) {}

    /**
     * @throws BadRequest
     * @throws Error
     * @throws Forbidden
     */
    public function filter(Data $data): void
    {
        $data->clear('parentId');
        $data->clear('relatedId');

        $contents = $this->handleContents($data);

        $relatedEntityType = $this->getRelatedEntityType($data);

        $field = $data->get('field');
        $role = $data->get('role') ?? Attachment::ROLE_ATTACHMENT;

        if (!$relatedEntityType || !$field) {
            throw new BadRequest("No `field` and `parentType`.");
        }

        $fieldData = new FieldData($field, $data->get('parentType'), $data->get('relatedType'));

        $this->accessChecker->check($fieldData, $role);
        $this->checkMaxSize($contents, $data, $field, $role);
    }

    private function getRelatedEntityType(Data $data): ?string
    {
        if ($data->get('parentType') !== null) {
            $data->clear('relatedType');

            return $data->get('parentType');
        }

        if ($data->get('relatedType') !== null) {
            return $data->get('relatedType');
        }

        return null;
    }

    /**
     * @throws BadRequest
     */
    private function handleContents(Data $data): string
    {
        $isBeingUploaded = $data->get('isBeingUploaded') ?? false;

        $contents = '';

        if (!$isBeingUploaded) {
            if (!$data->has('file')) {
                throw new BadRequest("No file contents.");
            }

            $file = $data->get('file');

            if (!is_string($file)) {
                throw new BadRequest("Non-string file contents.");
            }

            $arr = explode(',', $file);

            if (count($arr) < 2) {
                throw new BadRequest("Bad file contents.");
            }

            $contents = base64_decode($arr[1]);

            if ($contents === false) {
                throw new BadRequest("Could not decode file contents.");
            }
        }

        $data->set('contents', $contents);

        return $contents;
    }

    /**
     * @throws BadRequest
     */
    private function checkMaxSize(string $contents, Data $data, mixed $field, mixed $role): void
    {
        $size = mb_strlen($contents, '8bit');

        $dummy = $this->entityManager->getRepositoryByClass(Attachment::class)->getNew();

        $dummy->set([
            'parentType' => $data->get('parentType'),
            'relatedType' => $data->get('relatedType'),
            'field' => $field,
            'role' => $role,
        ]);

        $maxSize = $this->detailsObtainer->getUploadMaxSize($dummy);

        if ($maxSize && $size > $maxSize * 1024 * 1024) {
            throw new BadRequest("File size should not exceed $maxSize Mb.");
        }
    }
}
