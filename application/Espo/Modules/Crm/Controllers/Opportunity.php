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

namespace Espo\Modules\Crm\Controllers;

use Espo\Core\Acl\Table;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;
use Espo\Core\Field\Date;
use Espo\Modules\Crm\Entities\Opportunity as OpportunityEntity;
use Espo\Modules\Crm\Tools\Opportunity\Report\ByLeadSource;
use Espo\Modules\Crm\Tools\Opportunity\Report\ByStage;
use Espo\Modules\Crm\Tools\Opportunity\Report\DateRange;
use Espo\Modules\Crm\Tools\Opportunity\Report\SalesByMonth;
use Espo\Modules\Crm\Tools\Opportunity\Report\SalesPipeline;
use Espo\Modules\Crm\Tools\Opportunity\Service;
use stdClass;

class Opportunity extends Record
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionReportByLeadSource(Request $request): stdClass
    {
        if (!$this->acl->checkScope(OpportunityEntity::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $dateFrom = $request->getQueryParam('dateFrom');
        $dateTo = $request->getQueryParam('dateTo');
        $dateFilter = $request->getQueryParam('dateFilter');

        if (!$dateFilter) {
            throw new BadRequest("No `dateFilter` parameter.");
        }

        $range = new DateRange(
            $dateFilter,
            $dateFrom ? Date::fromString($dateFrom) : null,
            $dateTo ? Date::fromString($dateTo) : null
        );

        return $this->injectableFactory
            ->create(ByLeadSource::class)
            ->run($range);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionReportByStage(Request $request): stdClass
    {
        if (!$this->acl->checkScope(OpportunityEntity::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $dateFrom = $request->getQueryParam('dateFrom');
        $dateTo = $request->getQueryParam('dateTo');
        $dateFilter = $request->getQueryParam('dateFilter');

        if (!$dateFilter) {
            throw new BadRequest("No `dateFilter` parameter.");
        }

        $range = new DateRange(
            $dateFilter,
            $dateFrom ? Date::fromString($dateFrom) : null,
            $dateTo ? Date::fromString($dateTo) : null
        );

        return $this->injectableFactory
            ->create(ByStage::class)
            ->run($range);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionReportSalesByMonth(Request $request): stdClass
    {
        if (!$this->acl->checkScope(OpportunityEntity::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $dateFrom = $request->getQueryParam('dateFrom');
        $dateTo = $request->getQueryParam('dateTo');
        $dateFilter = $request->getQueryParam('dateFilter');

        if (!$dateFilter) {
            throw new BadRequest("No `dateFilter` parameter.");
        }

        $range = new DateRange(
            $dateFilter,
            $dateFrom ? Date::fromString($dateFrom) : null,
            $dateTo ? Date::fromString($dateTo) : null
        );

        return $this->injectableFactory
            ->create(SalesByMonth::class)
            ->run($range);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionReportSalesPipeline(Request $request): stdClass
    {
        if (!$this->acl->checkScope(OpportunityEntity::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $dateFrom = $request->getQueryParam('dateFrom');
        $dateTo = $request->getQueryParam('dateTo');
        $dateFilter = $request->getQueryParam('dateFilter');
        $useLastStage = $request->getQueryParam('useLastStage') === 'true';
        $teamId = $request->getQueryParam('teamId') ?? null;

        if (!$dateFilter) {
            throw new BadRequest("No `dateFilter` parameter.");
        }

        $range = new DateRange(
            $dateFilter,
            $dateFrom ? Date::fromString($dateFrom) : null,
            $dateTo ? Date::fromString($dateTo) : null
        );

        return $this->injectableFactory
            ->create(SalesPipeline::class)
            ->run($range, $useLastStage, $teamId);
    }

    /**
     * @return stdClass[]
     * @throws Forbidden
     * @throws BadRequest
     */
    public function getActionEmailAddressList(Request $request): array
    {
        $id = $request->getQueryParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        if (!$this->acl->checkScope(OpportunityEntity::ENTITY_TYPE, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $result = $this->injectableFactory
            ->create(Service::class)
            ->getEmailAddressList($id);

        return array_map(
            fn ($item) => $item->getValueMap(),
            $result
        );
    }
}
