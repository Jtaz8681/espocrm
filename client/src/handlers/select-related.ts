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

import type {SelectRelatedFilters, SelectRelatedHandler as SelectRelatedHandlerInterface} from 'contracts/relation';
import type ViewHelper from 'view-helper';
import type Model from 'model';

/**
 * Prepares filters for selecting records to relate.
 * Use the interface directly rather than extending this class.
 */
abstract class SelectRelatedHandler<M extends Model = Model> implements SelectRelatedHandlerInterface<M> {

    /**
     * @internal
     */
    protected readonly viewHelper: ViewHelper

    /**
     * @internal
     */
    constructor(viewHelper: ViewHelper) {
        this.viewHelper = viewHelper;
    }

    /**
     * @inheritDoc
     *
     * @param model A model.
     * @return {Promise<SelectRelatedFilters>} Filters.
     */
    async getFilters(model: M): Promise<SelectRelatedFilters> {
        // noinspection BadExpressionStatementJS
        model;

        return {};
    }
}

export default SelectRelatedHandler;
