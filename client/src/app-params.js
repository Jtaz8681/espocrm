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

/**
 * Application parameters.
 *
 * @since 9.0.0
 */
export default class AppParams {

    /**
     * @param {Record} params
     */
    constructor(params = {}) {
        /** @private */
        this.params = params;
    }

    /**
     * Get a parameter.
     *
     * @param {string} name A parameter.
     * @return {*}
     */
    get(name) {
        return this.params[name];
    }

    /**
     * Set all parameters.
     *
     * @internal
     * @param {Record} params
     */
    setAll(params) {
        this.params = params;
    }

    /**
     * Reload params from the backend.
     */
    async load() {
        /** @type {module:app~UserData} */
        const data = await Espo.Ajax.getRequest('App/appParams');

        this.params = data;
    }
}
