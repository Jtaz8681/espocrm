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

/** @module dynamic-handler */

import {View as BullView} from 'bullbone';

/**
 * A dynamic handler. To be extended by a specific handler.
 */
class DynamicHandler {

    /**
     * @param {module:views/record/detail} recordView A record view.
     */
    constructor(recordView) {

        /**
         * A record view.
         *
         * @protected
         * @type {module:views/record/detail}
         */
        this.recordView = recordView;

        /**
         * A model.
         *
         * @protected
         * @type {module:model}
         */
        this.model = recordView.model;
    }

    /**
     * Initialization logic. To be extended.
     *
     * @protected
     */
    init() {}

    /**
     * Called on model change. To be extended.
     *
     * @protected
     * @param {module:views/record/detail} model A model.
     * @param {Object} o Options.
     */
    onChange(model, o) {}

    /**
     * Get a metadata.
     *
     * @protected
     * @returns {module:metadata}
     */
    getMetadata() {
        return this.recordView.getMetadata()
    }
}

DynamicHandler.extend = BullView.extend;

// noinspection JSUnusedGlobalSymbols
export default DynamicHandler;
