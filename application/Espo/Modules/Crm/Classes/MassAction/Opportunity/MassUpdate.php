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

namespace Espo\Modules\Crm\Classes\MassAction\Opportunity;

use Espo\Core\MassAction\Actions\MassUpdate as MassUpdateOriginal;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\Result;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Tools\MassUpdate\Data as MassUpdateData;
use Espo\Core\Utils\Metadata;

class MassUpdate implements MassAction
{

    public function __construct(
        private MassUpdateOriginal $massUpdateOriginal,
        private Metadata $metadata
    ) {}

    public function process(Params $params, Data $data): Result
    {
        $massUpdateData = MassUpdateData::fromMassActionData($data);

        $probability = null;

        $stage = $massUpdateData->getValue('stage');

        if ($stage && !$massUpdateData->has('probability')) {
            $probability = $this->metadata->get("entityDefs.Opportunity.fields.stage.probabilityMap.$stage");
        }

        if ($probability !== null) {
            $massUpdateData = $massUpdateData->with('probability', $probability);
        }

        return $this->massUpdateOriginal->process($params, $massUpdateData->toMassActionData());
    }
}
