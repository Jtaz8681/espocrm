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

import type {SelectFieldHandler} from 'contracts/relation';
import type ViewHelper from 'view-helper';
import type Model from 'model';

/**
 * @since 10.0.0
 */
abstract class SelectFieldRelationHandler<
    M extends Model = Model,
    P extends Model = Model,
> implements SelectFieldHandler<M> {

    /**
     * @internal
     */
    protected viewHelper: ViewHelper

    /**
     * A parent model.
     */
    protected readonly model: P

    /**
     * @internal
     */
    constructor(viewHelper: ViewHelper, model: P) {
        this.viewHelper = viewHelper;
        this.model = model;
    }

    /**
     * @inheritDoc
     */
    abstract getAttributes(model: M): Promise<Record<string, unknown>>

    /**
     * @inheritDoc
     */
    abstract getClearAttributes(): Promise<Record<string, unknown>>
}

export default SelectFieldRelationHandler;
