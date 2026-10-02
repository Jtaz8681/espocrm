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

namespace Espo\Classes\FieldProcessing\LeadCapture;

use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Util;
use Espo\Entities\LeadCapture;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Type\AttributeType;

/**
 * @implements Loader<LeadCapture>
 */
class ExampleLoader implements Loader
{
    public function __construct(
        private FieldUtil $fieldUtil,
        private ApplicationConfig $applicationConfig,
        private EntityManager $entityManager,
        private Config $config,
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        $entity->set('exampleRequestMethod', 'POST');

        $entity->set('exampleRequestHeaders', [
            'Content-Type: application/json',
        ]);

        $this->processRequestUrl($entity);
        $this->processRequestPayload($entity);
        $this->processFormUrl($entity);
    }

    private function processRequestUrl(LeadCapture $entity): void
    {
        $apiKey = $entity->getApiKey();
        $siteUrl = $this->applicationConfig->getSiteUrl();

        if (!$apiKey) {
            return;
        }

        $requestUrl = "$siteUrl/api/v1/LeadCapture/$apiKey";

        $entity->set('exampleRequestUrl', $requestUrl);
    }

    private function processRequestPayload(LeadCapture $entity): void
    {
        $requestPayload = "```\n{\n";

        $attributeList = [];

        $attributeIgnoreList = [
            'emailAddressIsOptedOut',
            'phoneNumberIsOptedOut',
            'emailAddressIsInvalid',
            'phoneNumberIsInvalid',
            'emailAddressData',
            'phoneNumberData',
        ];

        foreach ($entity->getFieldList() as $field) {
            foreach ($this->fieldUtil->getActualAttributeList(Lead::ENTITY_TYPE, $field) as $attribute) {
                if (!in_array($attribute, $attributeIgnoreList)) {
                    $attributeList[] = $attribute;
                }
            }
        }

        $seed = $this->entityManager->getNewEntity(Lead::ENTITY_TYPE);

        foreach ($attributeList as $i => $attribute) {
            $value = strtoupper(Util::camelCaseToUnderscore($attribute));

            if (
                in_array(
                    $seed->getAttributeType($attribute), [
                        Entity::VARCHAR,
                        Entity::TEXT,
                        AttributeType::DATETIME,
                        AttributeType::DATE,
                    ]
                )
            ) {
                $value = '"' . $value . '"';
            }

            $requestPayload .= "    \"" . $attribute . "\": " . $value;

            if ($i < count($attributeList) - 1) {
                $requestPayload .= ",";
            }

            $requestPayload .= "\n";
        }

        $requestPayload .= "}\n```";

        $entity->set('exampleRequestPayload', $requestPayload);
    }

    private function processFormUrl(LeadCapture $entity): void
    {
        $formId = $entity->getFormId();
        $siteUrl = $this->getSiteUrl();

        if (!$entity->hasFormEnabled() || !$formId) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $entity->set('formUrl', null);

            return;
        }

        $formUrl = "$siteUrl?entryPoint=leadCaptureForm&id=$formId";

        $entity->set('formUrl', $formUrl);
    }

    private function getSiteUrl(): string
    {
        return $this->config->get('leadCaptureSiteUrl') ?? $this->applicationConfig->getSiteUrl();
    }
}
