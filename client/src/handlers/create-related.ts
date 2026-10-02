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

import type {CreateRelatedHandler as CreateRelatedHandlerInterface} from 'contracts/relation';
import type ViewHelper from 'view-helper';
import type Model from 'model';

/**
 * Prepares attributes for a related record that is being created.
 */
abstract class CreateRelatedHandler<M extends Model = Model> implements CreateRelatedHandlerInterface<M> {

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
     * Get attributes for a new record.
     *
     * @param model A model.
     * @param link A link name. As of v9.2.0.
     * @return {Promise<Object.<string, unknown>>} Attributes.
     */
    async getAttributes(model: M, link: string): Promise<Record<string, unknown>> {
        // noinspection BadExpressionStatementJS
        model;
        // noinspection BadExpressionStatementJS
        link;

        return {};
    }
}

export default CreateRelatedHandler;
