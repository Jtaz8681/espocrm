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

/** @module handlers/row-action */

/**
 * @abstract
 */
class RowActionHandler {

    /**
     * @param {module:views/record/list} view
     */
    constructor(view) {
        // noinspection JSUnusedGlobalSymbols
        /** @protected */
        this.view = view;

        /**
         * @protected
         * @type {module:collection}
         */
        this.collection = this.view.collection;
    }

    /**
     * @param {module:model} model A model.
     * @param {string} action An action.
     * @return {boolean}
     */
    isAvailable(model, action) {
        return true;
    }

    /**
     * @param {module:model} model A model.
     * @param {string} action An action.
     */
    process(model, action) {}
}

export default RowActionHandler;
